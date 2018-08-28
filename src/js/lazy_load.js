// Lazy load images on page by replacing src with data-src attribute after page load

document.addEventListener('DOMContentLoaded', function() {
    var lazyImages = document.querySelectorAll('img[data-src]'); 
    console.log(lazyImages); 
    lazyImages.forEach(function(image) {
        image.setAttribute('src', image.getAttribute('data-src')); 
        image.onload = function () {
            image.removeAttribute('data-src'); 
            image.removeAttribute('width'); 
            image.removeAttribute('height'); 
        }
    }); 

}); 