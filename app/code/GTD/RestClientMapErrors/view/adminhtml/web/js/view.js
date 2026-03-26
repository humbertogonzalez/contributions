define([
    'uiComponent',
    'jquery',
    'vuetify',
    'restClient',
    'vue-dev'
], function (Component, $, VuetifyRoot, restClient) {
    'use strict';
    const LOADER_ELEMENT = $('#map_errors .main-parent-loader');
    function hideLoader() {
        LOADER_ELEMENT.fadeOut('slow');
    }
    function showLoader() {
        LOADER_ELEMENT.fadeIn('slow');
    }
    return Component.extend({
        initialize: function () {
            this._super();
            let self = this;
            let Vuetify = VuetifyRoot.VueFun(window, Vue);
            const { createApp, reactive, ref } = Vue
            const { createVuetify } = Vuetify

            const vuetify = createVuetify()

            const app = createApp({
                setup() {
                    const errorsMap = self.errors.map((error) => {
                        error.loading = false;
                        if (error.default){
                            error.defaultLoading = false;
                            error.change = false;
                        }
                        error.list = error.list.map((errorItem) => {
                            errorItem.loadingDelete = false;
                            errorItem.loadingSave = false;
                            errorItem.change = false;
                            errorItem.new = false;
                            return errorItem;
                        })
                        return error;
                    })
                    const blocks = self.blocks;
                    const errors = reactive(errorsMap)
                    let displaySuccess = ref(false);
                    let displayError = ref(false);
                    let displaySuccessMessage = ref('');
                    let displayErrorMessage = ref('');
                    function addError(indexType) {
                        errors[indexType].list.push({
                            code_response: '',
                            block_code: '',
                            loadingDelete: false,
                            loadingSave: false,
                            change: false,
                            new: true
                        });
                    }

                    function deleteError(indexType, index) {
                        let entityId = errors[indexType].list[index].entity_id;
                        if (entityId) {
                            errors[indexType].list[index].loadingDelete = true;
                            errors[indexType].loading = true;
                            restClient.deleteRequest(
                            `${self.url_delete}errorId/${entityId}`
                            )
                                .done((data) => {
                                    if (data.success) {
                                        errors[indexType].list.splice(index, 1);
                                        displaySuccessMessage.value = `${errors[indexType].label}: Error Map eliminado con éxito`;
                                        displaySuccess.value = true;
                                    } else {
                                        displayErrorMessage.value = `${errors[indexType].label}: ${data.body}`;
                                        displayError.value = true;
                                    }
                                })
                                .error((error) => {
                                    let message = error.responseJSON ? error.responseJSON.body : 'Error Inesperado';
                                    errors[indexType].list.loadingDelete = false;
                                    displayErrorMessage.value = `${errors[indexType].label}: ${message}`;
                                    displayError.value = true;
                                })
                                .complete(() => {
                                    errors[indexType].loading = false;
                                })
                        } else {
                            errors[indexType].list.splice(index, 1);
                            displaySuccessMessage.value = `${errors[indexType].label}: Error Map eliminado con éxito`;
                            displaySuccess.value = true;
                        }
                    }

                    function saveError(indexType, index) {
                        errors[indexType].list[index].loadingSave = true
                        restClient.postRequest(
                            `${self.url_save}`,
                            {
                                error: errors[indexType].list[index],
                                errorType: indexType
                            }
                        )
                            .done((data) => {
                                if (data.success) {
                                    errors[indexType].list[index].entity_id = data.body.entity_id;
                                    errors[indexType].list[index].code_response = data.body.code_response;
                                    errors[indexType].list[index].block_code = data.body.block_code;
                                    errors[indexType].list[index].change = false;
                                    errors[indexType].list[index].new = false;
                                    displaySuccessMessage.value = `${errors[indexType].label}: Error Map actualizado con éxito`;
                                    displaySuccess.value = true;
                                } else {
                                    displayErrorMessage.value = `${errors[indexType].label}: ${data.body}`;
                                    displayError.value = true;
                                }
                            })
                            .error((error) => {
                                let message = error.responseJSON ? error.responseJSON.body : 'Error Inesperado';
                                displayErrorMessage.value = `${errors[indexType].label}: ${message}`;
                                displayError.value = true;
                            })
                            .complete(() => {
                                errors[indexType].list[index].loadingSave = false;
                            })
                    }

                    function saveDefault(indexType) {
                        errors[indexType].defaultLoading = true;
                        restClient.postRequest(
                            `${self.url_save_default}`,
                            {
                                error: errors[indexType].default,
                            }
                        )
                            .done((data) => {
                                if (data.success) {
                                    displaySuccessMessage.value = `${errors[indexType].label}: Default Error actualizado con éxito`;
                                    displaySuccess.value = true;
                                } else {
                                    displayErrorMessage.value = `${errors[indexType].label}: ${data.body}`;
                                    displayError.value = true;
                                }
                            })
                            .error((error) => {
                                let message = error.responseJSON ? error.responseJSON.body : 'Error Inesperado';
                                displayErrorMessage.value = `${errors[indexType].label}: ${message}`;
                                displayError.value = true;
                            })
                            .complete(() => {
                                errors[indexType].defaultLoading = false;
                            })
                    }

                    return {
                        errors,
                        addError,
                        deleteError,
                        saveDefault,
                        saveError,
                        displaySuccess,
                        displayError,
                        displaySuccessMessage,
                        displayErrorMessage,
                        blocks
                    }
                }
            })
            app.use(vuetify).mount('#app_vue')
            hideLoader();
        },
    });
});
