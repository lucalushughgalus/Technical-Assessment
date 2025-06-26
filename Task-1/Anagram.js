
    function isAnagram(w1,w2) {

        //Normalising, sorting and rejoining the strings
        sw1 = w1.replace(/\s+/g, '').toLowerCase().split('').sort().join('');
        sw2 = w2.replace(/\s+/g, '').toLowerCase().split('').sort().join('');


        if (sw1 === sw2) {
            console.log(w1 + " and " + w2 + " ARE anagrams of each other");
            return true;
        } else {
            console.log(w1 + " and " + w2 + " ARE NOT anagrams of each other");
            return false;
        }

    }

    //Correct result
    isAnagram("Dormitory", "dirty room");

    //Incorrect result
    isAnagram("Hello", "World");
