var lazy_loader=(()=>{"use strict";const dataAttr="data-LL-src";const imageLoaded="-js-lazyImageLoaded";const imageFadeClass="-js-fadeIn";const images=document.querySelectorAll("["+dataAttr+"]");const config={rootMargin:'-50px 0px',threshold:0.01};let imageCount=images.length;let observer;if(!('IntersectionObserver' in window)){Array.from(images).forEach(image=>preloadImage(image))}else{observer=new IntersectionObserver(onIntersection,config);images.forEach(image=>{if(image.classList.contains(imageLoaded)){return}
observer.observe(image)})}
function fetchImage(url){return new Promise((resolve,reject)=>{const image=new Image();image.src=url;image.onload=resolve;image.onerror=reject})}
function preloadImage(image){const src=image.getAttribute(dataAttr);if(!src){return}
return fetchImage(src).then(()=>{applyImage(image,src)})}
function loadImagesImmediately(images){Array.from(images).forEach(image=>preloadImage(image))}
function disconnect(){if(!observer){return}
observer.disconnect()}
function onIntersection(entries){if(imageCount===0){observer.disconnect()}
entries.forEach(entry=>{if(entry.intersectionRatio>0){imageCount--;observer.unobserve(entry.target);preloadImage(entry.target)}})}
function applyImage(img,src){img.classList.add(imageLoaded);img.src=src;img.classList.add(imageFadeClass)}});lazy_loader()