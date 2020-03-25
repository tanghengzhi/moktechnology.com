<?php
/*
This code is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

This code is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this code. If not, see http://www.gnu.org/licenses/gpl-2.0.html.
*/
?>

<!-- Start WP Responsive Age Verification - by DesignSmoke.com -->
<style>
#age-verification {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: <?php echo $ageOverlayColor; ?>;
  -webkit-transition: 500ms;
  transition: 500ms;
  z-index: 90000001;

  display: none;
}

.age-verification-main {
  background-color: <?php echo $ageDialogColor; ?>;
  font-family: "Source Sans Pro", sans-serif;
  color: white;
  font-size: 14pt;
  text-align: center;
  padding: 25px;

  position: relative;
  top: 10px;
  width: 500px;
  max-width: 80%;
  margin: 0 auto;
  -webkit-box-shadow: 1px 2px 9px 0px rgba(0,0,0,0.3);
  -moz-box-shadow: 1px 2px 9px 0px rgba(0,0,0,0.3);
  box-shadow: 1px 2px 9px 0px rgba(0,0,0,0.3);

  text-shadow: 0 0 7px rgba(0,0,0,0.3);
}
@media only screen and (min-height: 450px) {
  .age-verification-main {
    top: 40%;
  }
}

.age-title, .age-main-text {
  display: block;
  margin-bottom: 1em;
}
.age-title {
  font-size: 24pt;
  margin-bottom: 0.5em;
}

.age-button {
  cursor: pointer;
  
  -webkit-box-shadow: 1px 2px 9px 0px rgba(0,0,0,0.3);
  -moz-box-shadow: 1px 2px 9px 0px rgba(0,0,0,0.3);
  box-shadow: 1px 2px 9px 0px rgba(0,0,0,0.3);
}

.age-button {
  font-family: "Source Sans Pro", sans-serif;
  background-color: white;
  border: none;
  font-size: 16pt;

  color: <?php echo $ageDialogColor; ?>;

  display: inline-block;
  width: 150px;
  padding: 10px;
  margin: 5px 10px;
}

.age-credits {
  font-family: "Source Sans Pro", sans-serif;
  color: white;
  display: block;
  font-size: 12px;
  text-decoration: normal;
  text-align: right;
  margin-top: 20px;
  margin-bottom: -15px;
}
.age-credits a {
  color: white;
}
</style>

<div id="age-verification">
  <div class="age-verification-main">
    <span class="age-title"><?php echo $ageDialogTitle; ?></span>
    <span class="age-main-text"><?php echo $ageDialogText; ?></span>

    <button class="age-button age-yes" onclick="ragevAgeVerificationConfirm()"><?php echo $ageConfirmText; ?></button>
    <button class="age-button age-no" onclick="ragevAgeVerificationFailed()"><?php echo $ageDeclineText; ?></button>

    <?php if(intval($ageShowCredits) === 1): ?>
      <span class="age-credits">Using <a href="https://www.designsmoke.com">Responsive Age Verification Plugin for WordPress</a></span>
    <?php endif; ?>

  </div>
</div>

<script>
var ageCookieName = "resp-agev-age-verification-passed";

function ageSetCookie(cname, cvalue, exhours) {
    var d = new Date();
    d.setTime(d.getTime() + (exhours * 60 * 60 * 1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function ageGetCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function ragevAgeVerificationHide() {
  var ragevAgeVerificationModel = document.getElementById('age-verification');
  ragevAgeVerificationModel.style.display = 'none';
}
function ragevAgeVerificationShow() {
  var ragevAgeVerificationModel = document.getElementById('age-verification');
  ragevAgeVerificationModel.style.display = 'block';
}

function ragevAgeVerificationLoad() {
    try {
      var agePass = ageGetCookie(ageCookieName);
      var previewing = window.location.href.indexOf('preview_age_verification') > -1;
      if (agePass != "" && !previewing) {
        ragevAgeVerificationHide();
        return;
      }
      else {
        ragevAgeVerificationShow();
      }
    }
    catch(err) {
      ragevAgeVerificationShow();
    }
}

function ragevAgeVerificationConfirm() {
  ageSetCookie(ageCookieName, "verified", <?php echo intval($ageSessionDuration); ?>);
  ragevAgeVerificationHide();
}

function ragevAgeVerificationFailed() {
    window.history.back();

    if(window.parent != null) { //has a parent opener
        setTimeout(window.close, 150);
    }
}

/** EDIT: Run ASAP //OLD: Run the verification after DOM has been loaded **/
//document.addEventListener("DOMContentLoaded", function(event) {
  ragevAgeVerificationLoad();
//});
</script>
<!-- End WP Responsive Age Verification by DesignSmoke.com -->
