(() => {
    let a80 = document.querySelector('.a80');
    let a30 = document.querySelector('.a30');
    let a120 = document.querySelector('.a120');

    a80.innerHTML = "100 zł";
    a30.innerHTML = "40 zł";
    a120.innerHTML = "150 zł";

    let check = false;
    document.getElementById('flexSwitchCheckDefault').onchange = function () {

        if (check === true) {
            a80.innerHTML = "100 PLN";
            a30.innerHTML = "40 PLN"
            a120.innerHTML = "150 PLN"
            check = false;

            return;
        }

        a80.innerHTML = "80 PLN";
        a30.innerHTML = "30 PLN"
        a120.innerHTML = "120 PLN"
        check = true;
    }
})();