// Iterate through the elements with class obf
document.querySelectorAll('.obf').forEach(function (el) {
    if (el.dataset.obf !== undefined) {
        // Set href attribute of el to its [data-obf] attribute
        el.setAttribute('href', atob(el.dataset.obf));
        // Remove data-obf from element
        el.removeAttribute('data-obf');

        // Check for [data-target] attribute and set value to target attribute of el
        if (el.dataset.target !== undefined) {
            el.setAttribute('target', el.dataset.target);
            // Remove data-target from element
            el.removeAttribute('data-target');
        }

        // Create a link
        let link = document.createElement("a");
        // Set innerHTML of link to el's innerHTML
        link.innerHTML = el.innerHTML;
        // Transfer attributes of el to link element
        for (let i = 0; i < el.attributes.length; i++) {
            link.setAttribute(el.attributes[i].name, el.attributes[i].value);
        }
        // Replace el with link
        el.parentNode.replaceChild(link, el);
    }
});

