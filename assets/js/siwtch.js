(() => {
    const price = document.querySelectorAll('.price');
    const checkbox = document.getElementById('flexSwitchCheckDefault');

    function setPrice(checked) {
        price.forEach(el => {
            const netto = Number(el.getAttribute('data-netto'));
            const vat = Number(el.getAttribute('data-vat'));

            const price = checked ? netto : netto + (netto * vat);

            el.innerHTML = price.toFixed(2) + ' zł';
        });
    }

    checkbox.onchange = ev => setPrice(ev.target.checked);
    setPrice(false);
})();