function success() {
    // success-div nach 5s (5000ms) löschen
    setTimeout(function(){
        var div = document.getElementById('dialog');
        // div existiert -> div löschen
        if(div) {
            div.parentNode.removeChild(div);
        }
    },5000);
}

// source: https://github.com/kvz/phpjs/blob/master/functions/strings/number_format.js
function number_format(number, decimals, dec_point, thousands_sep) {
    // example 1: number_format(1234.56,2,',','');
    // returns 1: '1234,56'

    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function(n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

function calcKraftstoffkosten(preisproliter) {
    // Eingabe
    var liter = document.getElementById('liter').value;
    var elemkosten = document.getElementById('kosten');
    var kosten='';
    // Verarbeitung
    // liter numerisch
    if(!isNaN(liter)) {
        kosten = number_format(liter*preisproliter,2,',','');
        elemkosten.setAttribute("value",kosten);
    }
    else {
        // eventuel numerisch nach , suchen
        if(liter.indexOf(',')>0) {
            // , durch . ersetzen
            liter = liter.replace(',','.');
            // liter doch numerisch
            if(!isNaN(liter)) {
                // kosten berechnen
                kosten = number_format(liter*preisproliter,2,',','');
                elemkosten.setAttribute("value",kosten);
            }
        }
    }
}