// Lazy load images on page by replacing src with data-src attribute after page load

// [].forEach.call(document.querySelectorAll('img[data-src]'), function(img) {
//     img.setAttribute('src', img.getAttribute('data-src')); 
//     img.onload = function () {
//         img.removeAttribute('data-src'); 
//     };
// }); 

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