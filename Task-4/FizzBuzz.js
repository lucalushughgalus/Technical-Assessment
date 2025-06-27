var x = 1;
(function(){
    var meaningless = function(y) {
        var ugh = '';
        if (((y / 3) + '').indexOf('.') < 0) {
            ugh = ugh + 'Fizz';
        }
        if (!(y % 5)) {
            for (var i = 0; i < 1; i++) {
                ugh += 'Buzz';
            }
        }
        if (ugh == '') {
            var arr = ['0','1','2','3','4','5','6','7','8','9'];
            var n = y + '';
            var result = '';
            for (var j = 0; j < n.length; j++) {
                if (arr.includes(n[j])) {
                    result += n[j];
                }
            }
            ugh = result;
        }
        console.log(ugh);
    };
    meaningless.log = function(s) {
            console.log(s);
    };
    var something = setInterval(function() {
        meaningless(x++);
        if (x > 100) clearInterval(something);
    }, 1);
})();
