import Client from './Client/AxiosClient'
import { baseUrl } from './Client/AxiosClient'

const resource = `${baseUrl}/api/agents/commissions`

export default {
    list () {
        return Client.get(`${resource}`)
    },
    create (commission) {
        return Client.post(`${resource}`, commission)
    },
    update (commission) {

        return Client.put(`${resource}/${commission.id}`, commission)
    },
    delete (commissionId) {
        return Client.delete(`${resource}/${commissionId}`)
    }

}
