<template>
    <form>
        <label>YOUR NAME
            <input class="about-name" v-model="name">
        </label>
        <label>
            EMAIL
            <input class="about-email" v-model="email">
        </label>
        <label>
            MESSAGE
            <textarea class="about-message" v-model="message"></textarea>
        </label>
        <div class="text-right">
            <button class="about-contact-submit" @click="submitContactForm">Submit</button>
        </div>
        <div class="about-form-error callout alert" data-closable v-if="!errorHidden" v-cloak>
            <p class="contact-form-error-msg">{{ errorMsg }}</p>
            <button class="close-button" aria-label="Dismiss alert" type="button" @click="closeContactAlert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="subscribe-form-success callout success" data-closable v-if="!successHidden" v-cloak>
            <p>Thanks, we'll respond to your message shortly!</p>
            <button class="close-button" aria-label="Dismiss alert" type="button" @click="closeContactSuccess">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </form>
</template>

<script>
    export default {
        name: "contact-form.vue",
        data: function() {
            return {
                name: '',
                email: '',
                message: '',
                errorMsg: '',
                errorHidden: true,
                successHidden: true,
            }
        },
        methods: {
            submitContactForm: function(event) {
                event.preventDefault();
                this.successHidden = true;
                this.errorHidden = true;

                if (window.SC.emailIsValid(this.email) === false)
                    this.errorMsg = 'Your email address is invalid.';
                else
                    this.errorMsg = '';

                if (this.errorMsg !== '') {
                    this.showErrorMsg(this.errorMsg);

                } else {
                    //send off the form data.
                    let self = this;
                    let params = {};
                    params.email = this.email;
                    params.name = this.name;
                    params.message = this.message;

                    axios({
                        method: 'post',
                        url: '/contact',
                        headers: {'X-CSRF-TOKEN': window.SC.csrfToken},
                        data: params,
                    })
                        .then(function (response) {
                            if (response.data.valid) {
                                self.successHidden = false;
                            } else {
                                self.showErrorMsg(response.data.errorMsg);
                            }
                        })
                        .catch(function (error) {
                            if (typeof(error.response) !== 'undefined' && error.response.status === 422) {
                                //get first error message in errors object
                                let firstProp;
                                let jsonObj = error.response.data.errors;
                                for(var key in jsonObj) {
                                    if(jsonObj.hasOwnProperty(key)) {
                                        firstProp = jsonObj[key];
                                        break;
                                    }
                                }
                                self.showErrorMsg(firstProp[0]);
                            } else
                                self.showErrorMsg( 'There was a problem processing the form');
                        });

                }
            },
            closeContactAlert: function() {
                this.errorHidden = true;
            },
            closeContactSuccess: function() {
                this.successHidden = true;
            },
            showErrorMsg: function(errorMsg) {
                this.errorMsg = errorMsg;
                this.errorHidden = false;
            },
        }
    }
</script>

<style scoped lang="scss">

</style>
