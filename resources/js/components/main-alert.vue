<template>
    <div id="nav-alert" v-html="alertTxt" v-if="alertTxt"></div>
</template>

<script>
    export default {
        name: "main-alert.vue",
        data() {
            return {
                alertTxt:false
            }
        },
        created() {
            let self = this;

            //fetch alert
            axios({
                method: 'get',
                url: '/api/alert',
                headers: {'X-CSRF-TOKEN': window.SC.csrfToken}
            })
                .then(function (response) {
                    if (response.data.alertTxt)
                        self.alertTxt = response.data.alertTxt;
                })
                .catch(function (error) {
                    console.log('There was an error fetching alert');
                });
        }
    }
</script>

<style scoped>

</style>
