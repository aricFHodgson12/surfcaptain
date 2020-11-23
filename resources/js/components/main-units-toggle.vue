<template>
    <div id="units-toggle">
        <div v-if="label" class="units-toggle-label">{{ label }}</div>
        <div class="nav-units">
            <div class="nav-links-units-unit" :class="{ 'active': (dataUnit.id === selectedUnit) }" v-html="dataUnit.name" v-for="dataUnit in dataUnits"
                 :id="dataUnit.id" @click="changeUnits(dataUnit.id)"
            ></div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "units-toggle.vue",
        props: ['label','variable','units','selected'],
        data() {
            return {
                dataUnits: [
                    {
                        'id': 1,
                        'name': '<span data-tooltip tabindex="1" title="Imperial Units" data-trigger-class="units-tip">FT</span>'
                    },
                    {
                        'id': 2,
                        'name': '<span data-tooltip tabindex="1" title="Metric Units" data-trigger-class="units-tip">M</span>'
                    }
                ],
                selectedUnit: 1
            }
        },
        created() {

            if (this.variable === 'cookie') {
                let scUnits = window.SC.getCookie('sc_units');
                if (scUnits !== undefined)
                    this.selectedUnit = parseInt(scUnits);

            } else {
                this.dataUnits = this.units;
                this.selectedUnit = this.selected;
            }

        },
        methods: {
            changeUnits(unitId) {
                this.selectedUnit = unitId;

                if (this.variable === 'cookie') {
                    window.SC.setCookie('sc_units', unitId);
                    this.$root.reloadFcstData = true;

                } else
                    this.$emit(this.variable+'change',unitId);
            }
        }
    }
</script>

<style scoped lang="scss">

</style>
