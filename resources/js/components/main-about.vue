<template>
    <div id="about">
        <section id="about-info">
            <div class="about-info-label">Our Mission</div>
            <h1>Connecting People to Their Passion for Surfing</h1>
            <p>What inspires us at Surf Captain is connecting our users to their passion: SURFING.
                Awesome surf forecasts make Surf Captain the direct link to knowing the next great day of surfing.
                A great day on the water makes everything better, and we love being a part of that.
            </p>
            <img src="/images/happy-surfer-paddling-small.jpg" />
            <a id="story"></a>
            <div class="about-info-label about-dc">About Surf Captain</div>
            <h2>Awesome Forecasts, Made Simple</h2>
            <p>Our team has decades of experience in forecasting surf conditions -- we've been doing it since the 1990s.
                We've studied every component of surf forecasting to determine the optimal approach. Surf Captain's own wave
                and weather models and advanced scientific techniques provide accurate surf height and wind condition predictions.
                While our process is complex, our core focus is making an accurate forecast simple for our users. For more info on the
                Surf Captain forecasts <a href="/faq">Check Out our FAQ page</a>.
            </p>
        </section>
        <a id="contact"></a>
        <section id="about-contact">
            <div class="about-contact-content">
                <div class="about-contact-label">Contact</div>
                <h1>We'd love to hear from you!</h1>
                <sc-contact-form></sc-contact-form>
            </div>
        </section>
    </div>

</template>

<script>
	export default {
	    name: 'About',    
	    metaInfo() {
	        return { 
	            title: "About Surf Captain"
	        }
	    }
	}

</script>

<style lang="scss" scoped>
	@import '../../sass/about.scss';
</style>    