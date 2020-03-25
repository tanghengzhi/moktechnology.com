<?php

global $age_restriction;

/*
from: http://www.state.gov/misc/list/

var $blocks = $('blockquote[dir="ltr"]');
$blocks.each(function (i,e) {
    var $that = $(this), $rows = $that.find('> p > a');
    $rows.each(function (i2, e2) {
        var country = $(this).text();
        country = $.trim(country);
        country = country.replace(/'/g, "\\'");
        console.log( "'" + country + "' => '" + country
        + "',");
    });
});
*/

$age_restriction_countries_list = $age_restriction->getCountriesList('code');
?>