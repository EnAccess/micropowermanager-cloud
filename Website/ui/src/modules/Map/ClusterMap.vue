<template>
    <div id="map">

    </div>
</template>

<script>
import { sharedMap, notify } from '@/mixins'
import { EventBus } from '@/shared/eventbus'
import { ICON_OPTIONS, ICONS, MARKER_TYPE } from '@/services/MappingService'

export default {
    name: 'ClusterMap',
    mixins: [sharedMap, notify],
    mounted () {
        const drawingLayer = this.editableLayer
        const service = this.mappingService
        this.map.on('draw:created', (item) => {
            const layer = item.layer
            const feature = layer.feature = layer.feature || {}
            feature.type = feature.type || 'Feature'
            const props = feature.properties = feature.properties || {}
            props.draw_type = 'draw'
            props.selected = false
            drawingLayer.addLayer(layer)

            const { sumLat, sumLon } = layer._latlngs[0].reduce((acc, coordinates) => {
                acc.sumLat += coordinates.lat
                acc.sumLon += coordinates.lng
                return acc
            }, { sumLat: 0, sumLon: 0 })

            const avgLat = sumLat / layer._latlngs[0].length
            const avgLon = sumLon / layer._latlngs[0].length
            const geoDataItem = service.manualDrawingLocationConvert({
                leaflet_id: layer._leaflet_id,
                type: 'manual',
                geojson: {
                    type: 'Polygon',
                    coordinates: layer._latlngs,
                },
                display_name: '',
                selected: false,
                draw_type: 'draw',
                lat: avgLat,
                lon: avgLon,
            })
            // this.$emit throws error interestingly
            EventBus.$emit('customDrawnSet', geoDataItem)
        })
        this.map.on('draw:deleted', (item) => {
            const deletedItems = []
            const deletedLayers = item.layers
            deletedLayers.eachLayer((layer) => {
                deletedItems.push(layer)
            })
            this.$emit('customDrawnDeleted', deletedItems)
        })
        this.map.on('draw:edited', (item) => {
            const editedItems = []
            const editedLayers = item.layers
            editedLayers.eachLayer((layer) => {
                const { sumLat, sumLon } = layer._latlngs[0].reduce((acc, coordinates) => {
                    acc.sumLat += coordinates.lat
                    acc.sumLon += coordinates.lng
                    return acc
                }, { sumLat: 0, sumLon: 0 })

                const avgLat = sumLat / layer._latlngs[0].length
                const avgLon = sumLon / layer._latlngs[0].length
                const geoDataItem = service.manualDrawingLocationConvert({
                    leaflet_id: layer._leaflet_id,
                    type: 'manual',
                    geojson: {
                        type: 'Polygon',
                        coordinates: layer._latlngs,
                    },
                    display_name: '',
                    selected: false,
                    lat: avgLat,
                    lon: avgLon,
                })
                editedItems.push(geoDataItem)

            })
            this.$emit('customDrawnEdited', editedItems)
        })
    },
    methods: {
        drawCluster () {
            this.editableLayer.clearLayers()
            const geoData = this.mappingService.geoData
            const geoType = geoData.geojson.type
            const coordinatesClone = geoData.geojson.coordinates[0].reduce((acc, coord) => {
                acc[0].push([coord[1], coord[0]])
                return acc
            }, [[]])
            const drawing = {
                type: 'FeatureCollection',
                crs: {
                    type: 'name',
                    properties: {
                        name: 'urn:ogc:def:crs:OGC:1.3:CRS84'
                    }
                },
                features: [{
                    type: 'Feature',
                    properties: {
                        popupContent: geoData.display_name,
                        draw_type: geoData.draw_type === undefined ? 'set' : geoData.draw_type,
                        selected: geoData.selected === undefined ? false : geoData.selected,
                        clusterId: geoData.clusterId === undefined ? -1 : geoData.clusterId,
                    },
                    geometry: {
                        type: geoType,
                        coordinates: geoData.searched ? geoData.geojson.coordinates : coordinatesClone
                    }
                }]
            }
            const polygonColor = this.mappingService.strToHex(geoData.display_name)
            // "this"  cannot be used inside the L.geoJson function
            const editableLayer = this.editableLayer
            const geoDataItems = this.geoDataItems
            const drawnCluster = L.geoJson(drawing, {
                style: { fillColor: polygonColor, color: polygonColor },
                onEachFeature: function (feature, layer) {
                    const type = layer.feature.geometry.type
                    const clusterId = layer.feature.properties.clusterId
                    if (type === 'polygon' && clusterId !== -1) {
                        layer.on('click', () => { this.$router.push({ path: '/clusters/' + clusterId })})
                    }
                    editableLayer.addLayer(layer)
                    const geoDataItem = {
                        leaflet_id: layer._leaflet_id,
                        type: 'manual',
                        geojson: {
                            type: geoData.geojson.type,
                            coordinates: geoData.searched === true ? coordinatesClone : geoData.geojson.coordinates
                        },
                        searched: false,
                        display_name: geoData.display_name,
                        selected: feature.properties.selected,
                        draw_type: feature.properties.draw_type,
                        lat: geoData.lat,
                        lon: geoData.lon,
                    }
                    geoDataItems.push(geoDataItem)
                }
            })
            const bounds = drawnCluster.getBounds()
            this.map.fitBounds(bounds)
        },
        setMiniGridMarkers () {
            const dataLoggerActiveMiniGrids = L.featureGroup.subGroup(this.markersLayer)
            const dataLoggerInactiveMiniGrids = L.featureGroup.subGroup(this.markersLayer)
            const control = L.control.layers(null, null, { collapsed: false })
            control.addOverlay(dataLoggerActiveMiniGrids, 'Data Stream Active')
            control.addOverlay(dataLoggerInactiveMiniGrids, 'Data Stream Inactive')
            control.addTo(this.map)
            this.mappingService.markingInfos.filter((markingInfo) => markingInfo.markerType === MARKER_TYPE.MINI_GRID).map((markingInfo) => {
                const miniGridMarkerIcon = L.icon({
                    ...ICON_OPTIONS,
                    iconUrl: ICONS[markingInfo.markerType]
                })
                const miniGridMarker = L.marker([markingInfo.lat, markingInfo.lon], { icon: miniGridMarkerIcon })
                miniGridMarker.bindTooltip('Mini Grid: ' + markingInfo.name)
                const parent = this
                miniGridMarker.on('click', function () {
                    parent.routeToDetail(`/dashboards/mini-grid`,markingInfo.id)
                })
                if (markingInfo.dataStream > 0) {
                    miniGridMarker.addTo(dataLoggerActiveMiniGrids)
                } else {
                    miniGridMarker.addTo(dataLoggerInactiveMiniGrids)
                }
                miniGridMarker.addTo(this.markersLayer)
            })
            this.map.addLayer(this.markersLayer)
            this.map.addLayer(dataLoggerActiveMiniGrids)
            this.map.addLayer(dataLoggerInactiveMiniGrids)
        },
    }
}
</script>

<style scoped>
#map {
    height: 100%;
    min-height: 500px;
    width: 100%;
}

.leaflet-draw-actions a {
    background: white !important;
}
</style>