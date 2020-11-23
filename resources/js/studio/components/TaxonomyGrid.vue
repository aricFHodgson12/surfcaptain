<template>
    <div v-if="availableItems.length > 0" id="topic-list">
        <div class="grid-x grid-margin-x">
            <div v-for="item in availableItems" class="cell small-6 medium-4 topic-item">
                <router-link :to="{ name: type+'-posts', params: { slug: item.slug } }" class="text-decoration-none">
                    <div class="card">
                        <div class="card-section">
                            <p>{{ item.name }}</p>
                        </div>
                    </div>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'taxonomy-grid',

        props: {
            items: {
                type: Array,
                required: true,
            },
            type: {
                type: String,
                required: true
            }
        },

        data() {
            return {
                availableItems: this.items,
            }
        }
    }
</script>

<style lang="scss">

    #topic-list {

        .topic-item {
            margin-bottom: 1.5rem!important;
        }
    }

</style>
