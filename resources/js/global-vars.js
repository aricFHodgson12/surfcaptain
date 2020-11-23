//global object to attach functions and variables
window.SC = {};

//global javascript functions and variables
window.SC.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
//input hidden field for csrf-token, to be added to form
window.SC.csrfInput = document.createElement("input");
window.SC.csrfInput.setAttribute("type", "hidden");
window.SC.csrfInput.setAttribute("name", "_token");
window.SC.csrfInput.setAttribute("value", window.SC.csrfToken);

window.SC.isVisible = elem => !!elem && !!( elem.offsetWidth || elem.offsetHeight || elem.getClientRects().length );

window.SC.width = window.innerWidth;
window.SC.height = window.innerHeight;


/*
window.SC.convertTemp = function(fahrenheit,unit,precision=0) {
    if (unit === 'f')
        return fahrenheit;
    else {
        return ((fahrenheit - 32) / 1.8).toFixed(precision); //to celcius
    }
};

window.SC.convertHeight = function(feet,unit,precision=1) {
    if (unit === 'ft')
        return feet;
    else
        return (feet / 3.281).toFixed(precision);
};

window.SC.convertWindSpeed = function(windSpd, units, precision=0) {
    if (units === 'kph')
        return (windSpd * 1.60934).toFixed(precision);
    else if (units === 'kt')
        return (windSpd * 0.868976).toFixed(precision);
    else
        return windSpd;
};
*/

window.SC.preloadImage = function(url) {
    const img=new Image();
    img.src=url;
};

window.SC.emailIsValid = function(email) {
    return /\S+@\S+\.\S+/.test(email)
};

window.SC.getCookie = function(name) {
    var value = "; " + document.cookie;
    var parts = value.split("; " + name + "=");
    if (parts.length === 2) return parts.pop().split(";").shift();
};

window.SC.setCookie = function(cname, cvalue, exdays = 365) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires + "; path=/";
};

window.SC.removeCookie = function(cname) {
    window.SC.setCookie(cname,'',exdays=-1);
};

window.SC.titleCase = function(str) {
    var splitStr = str.toLowerCase().split(' ');
    for (var i = 0; i < splitStr.length; i++) {
        // You do not need to check if i is larger than splitStr length, as your for does that for you
        // Assign it back to the array
        splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);
    }
    // Directly return the joined string
    return splitStr.join(' ');
};

window.SC.capitalize = (s) => {
    if (typeof s !== 'string') return '';
    return s.charAt(0).toUpperCase() + s.slice(1)
};

window.SC.upperCase = (s) => {
    if (typeof s !== 'string') return '';
    return s.toUpperCase();
};

window.SC.viewportWidth = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
window.SC.viewportHeight = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);

window.SC.slideUp = (target, duration=500) => {
    target.style.transitionProperty = 'height, margin, padding';
    target.style.transitionDuration = duration + 'ms';
    target.style.boxSizing = 'border-box';
    target.style.height = target.offsetHeight + 'px';
    target.offsetHeight;
    target.style.overflow = 'hidden';
    target.style.height = 0;
    target.style.paddingTop = 0;
    target.style.paddingBottom = 0;
    target.style.marginTop = 0;
    target.style.marginBottom = 0;
    window.setTimeout( () => {
        target.style.display = 'none';
        target.style.removeProperty('height');
        target.style.removeProperty('padding-top');
        target.style.removeProperty('padding-bottom');
        target.style.removeProperty('margin-top');
        target.style.removeProperty('margin-bottom');
        target.style.removeProperty('overflow');
        target.style.removeProperty('transition-duration');
        target.style.removeProperty('transition-property');
    }, duration);
};

window.SC.slideDown = (target, duration=500) => {
    target.style.removeProperty('display');
    let display = window.getComputedStyle(target).display;

    if (display === 'none')
        display = 'block';

    target.style.display = display;
    let height = target.offsetHeight;
    target.style.overflow = 'hidden';
    target.style.height = 0;
    target.style.paddingTop = 0;
    target.style.paddingBottom = 0;
    target.style.marginTop = 0;
    target.style.marginBottom = 0;
    target.offsetHeight;
    target.style.boxSizing = 'border-box';
    target.style.transitionProperty = "height, margin, padding";
    target.style.transitionDuration = duration + 'ms';
    target.style.height = height + 'px';
    target.style.removeProperty('padding-top');
    target.style.removeProperty('padding-bottom');
    target.style.removeProperty('margin-top');
    target.style.removeProperty('margin-bottom');
    window.setTimeout( () => {
        target.style.removeProperty('height');
        target.style.removeProperty('overflow');
        target.style.removeProperty('transition-duration');
        target.style.removeProperty('transition-property');
    }, duration);
};
window.SC.slideToggle = (target, duration = 500) => {
    if (window.getComputedStyle(target).display === 'none' || window.getComputedStyle(target).visibility === 'hidden') {
        return window.SC.slideDown(target, duration);
    } else {
        return window.SC.slideUp(target, duration);
    }
};

window.SC.toggle = (target) => {
    let newDisplay = 'none';
    if (target.style.display === 'none' || target.style.display === '')
        newDisplay = 'block';

    target.style.display = newDisplay;
};

window.SC.toggleClass = (target, elClass) => {
    if (target.classList.contains(elClass))
        target.classList.remove(elClass);
    else
        target.classList.add(elClass);
};

window.SC.hide = (target) => {
    target.style.display = 'none';
};

//set urlParams
(window.onpopstate = function () {
    let match,
        pl     = /\+/g,  // Regex for replacing addition symbol with a space
        search = /([^&=]+)=?([^&]*)/g,
        decode = function (s) { return decodeURIComponent(s.replace(pl, " ")); },
        query  = window.location.search.substring(1);

    window.SC.urlParams = {};
    while (match = search.exec(query))
        window.SC.urlParams[decode(match[1])] = decode(match[2]);

    window.SC.controller = window.location.pathname.split('/')[1];

})();
