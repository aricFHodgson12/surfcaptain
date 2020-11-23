<template>
    <div class="fcst-range-8-16">
        <div class="fcst-range-btn" @click="$emit('changedayrange',prevDayRange)">
            <img src="/images/icon/arrow_left.svg" class="arrow-left">
        </div>
        <span class="fcst-days-range">{{ dayRange }}</span> of 16
        <div class="fcst-range-btn" @click="$emit('changedayrange',nextDayRange)">
            <img src="/images/icon/arrow_right.svg" class="arrow-right">
        </div>
    </div>
</template>

<script>
    export default {
        name: "fcst-day-range.vue",
        props: ['dayrangeval'],
        computed: {
            dayRange() {
                if (this.dayrangeval === 6)
                    return '1 - 6';
                else if (this.dayrangeval === 12)
                    return '6 - 11';
                else if (this.dayrangeval === 10)
                    return '11 - 16';
                else if (this.dayrangeval === 8)
                    return '1 - 8';
                else
                    return '9 - 16';
            },
            prevDayRange() {
                if (this.dayrangeval === 6)
                    return 6;
                else if (this.dayrangeval === 12)
                    return 6;
                else if (this.dayrangeval === 10)
                    return 12;
                else if (this.dayrangeval === 8)
                    return 8;
                else
                    return 8;
            },
            nextDayRange() {
                if (this.dayrangeval === 6)
                    return 12;
                else if (this.dayrangeval === 12)
                    return 10;
                else if (this.dayrangeval === 10)
                    return 10;
                else if (this.dayrangeval === 8)
                    return 16;
                else
                    return 16;
            }
        }
    }
</script>

<style scoped>

</style>
