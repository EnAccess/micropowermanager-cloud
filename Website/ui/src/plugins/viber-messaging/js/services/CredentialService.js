import Repository from '../repositories/RepositoryFactory'
import { ErrorHandler } from '../Helpers/ErrorHander'

export class CredentialService {
    constructor() {
        this.repository = Repository.get('credential')
        this.credential = {
            id: null,
            apiToken: null,
            deepLink: null,
        }
    }

    fromJson(credentialData) {
        this.credential = {
            id: credentialData.id,
            apiToken: credentialData.api_token,
            deepLink: credentialData.deep_link,
            hasWebhookCreated: credentialData.has_webhook_created,
        }
        return this.credential
    }

    async getCredential() {
        try {
            let response = await this.repository.get()
            if (response.status === 200) {
                return this.fromJson(response.data.data)
            } else {
                return new ErrorHandler(response.error, 'http', response.status)
            }
        } catch (e) {
            let errorMessage = e.response.data.data.message
            return new ErrorHandler(errorMessage, 'http')
        }
    }

    async updateCredential() {
        try {
            let credentialPM = {
                id: this.credential.id,
                api_token: this.credential.apiToken,
            }
            let response = await this.repository.put(credentialPM)
            if (response.status === 200 || response.status === 201) {
                return this.fromJson(response.data.data)
            } else {
                return new ErrorHandler(response.error, 'http', response.status)
            }
        } catch (e) {
            let errorMessage = e.response.data.data.message
            return new ErrorHandler(errorMessage, 'http')
        }
    }
}
