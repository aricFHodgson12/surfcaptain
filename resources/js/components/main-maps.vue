<template>
    <div id="maps">
        <div id="maps-coming-soon">
            <img src="/images/maps-coming-soon.jpg" />
            <div id="coming-soon-overlay"></div>
            <h1>Wind, Swell, and Buoy Maps made awesome <br> Coming soon...</h1>
        </div>
    </div>

</template>

<script>
	export default {
	    name: 'Map',    
	    metaInfo() {
	        return { 
	            title: "Surf Captain Weather and Swell Maps"
	        }
	    }
	}

</script>

<style lang="scss" scoped>
	@import '../../sass/maps.scss';
</style>    