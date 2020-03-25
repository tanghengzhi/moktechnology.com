(function($){var h5={defaults:{debug:false,patternLibrary:{phone:/([\+][0-9]{1,3}([ \.\-])?)?([\(]{1}[0-9]{3}[\)])?([0-9A-Z \.\-]{1,32})((x|ext|extension)?[0-9]{1,4}?)/,email:/((([a-zA-Z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-zA-Z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-zA-Z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-zA-Z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-zA-Z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-zA-Z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-zA-Z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-zA-Z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-zA-Z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-zA-Z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?/,url:/(https?|ftp):\/\/(((([a-zA-Z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-zA-Z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-zA-Z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-zA-Z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-zA-Z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-zA-Z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-zA-Z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-zA-Z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-zA-Z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-zA-Z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-zA-Z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-zA-Z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-zA-Z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?/,number:/-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d+)?/,dateISO:/\d{4}[\/\-]\d{1,2}[\/\-]\d{1,2}/,alpha:/[a-zA-Z]+/,alphaNumeric:/\w+/,integer:/-?\d+/},messages:{required:'This is a required field.',invalid:'Please correct this field.'},classPrefix:'h5-',errorClass:'ui-state-error',validClass:'ui-state-valid',activeClass:'active',requiredClass:'required',requiredAttribute:'required',patternAttribute:'pattern',errorAttribute:'data-h5-errorid',kbSelectors:':text, :password, select, textarea',focusout:true,focusin:false,change:true,keyup:false,mSelectors:':radio, :checkbox, select, option',click:true,activeKeyup:true,requiredVar:'h5-required',patternVar:'h5-pattern',stripMarkup:true,submit:true,invalidCallback:function(){},validCallback:function(){},validateOnSubmit:true,allValidSelectors:'input:visible, textarea:visible, select:visible',markInvalid:function(options){var $element=$(options.element),$errorID=$(options.errorID);$element.addClass(options.errorClass).removeClass(options.validClass);$element.addClass(options.settings.activeClass);if($errorID.length){if($element.attr('title')){$errorID.text($element.attr('title'));}
$errorID.show();$errorID.delay(1200).fadeOut('slow');}
$element.data('valid',false);options.settings.invalidCallback.call(options.element);return $element;},markValid:function(options){var $element=$(options.element),$errorID=$(options.errorID);$element.addClass(options.validClass).removeClass(options.errorClass);if($errorID.length){$errorID.hide();}
$element.data('valid',true);options.settings.validCallback.call(options.element);return $element;},unmark:function(options){var $element=$(options.element);$element.removeClass(options.errorClass).removeClass(options.validClass);$element.form.find("#"+options.element.id).removeClass(options.errorClass).removeClass(options.validClass);return $element;}}},defaults=h5.defaults,messages=defaults.messages,patternLibrary=defaults.patternLibrary,methods={isValid:function(settings){var $this=$(this);settings.validate.call(this,settings);return $this.data('valid');},allValid:function(settings){var valid=true;$(this).find(settings.allValidSelectors).each(function(){valid=$(this).h5Validate('isValid')&&valid;});return valid;},validate:function(settings){var $this=$(this),pattern=$this.filter('[pattern]')[0]?$this.attr('pattern'):false,re=new RegExp('^(?:'+pattern+')$'),value=($this.is('[type=checkbox]')||$this.is('[type=radio]'))?$this.is(':checked'):$this.val(),errorClass=settings.errorClass,validClass=settings.validClass,errorIDbare=$this.attr(settings.errorAttribute)||false,errorID=errorIDbare?'#'+errorIDbare:false,required=false,isValid=true,reason='',$checkRequired=$('<input required>');if($checkRequired.filter('[required]')&&$checkRequired.filter('[required]').length){required=($this.filter('[required]').length&&$this.attr('required')!=='false')?true:false;}else{required=($this.attr('required')!==undefined)?true:false;}
if(settings.debug&&window.console){console.log('Validate called on "'+value+'" with regex "'+re+'". Required: '+required);console.log('Regex test: '+re.test(value)+', Pattern: '+pattern);}
if(required&&!value){isValid=false;reason='required';}else if(pattern&&!re.test(value)&&value){isValid=false;reason='pattern';}else{isValid=true;settings.markValid({element:this,errorClass:errorClass,validClass:validClass,errorID:errorID,settings:settings});}
if(!isValid){settings.markInvalid({element:this,reason:reason,errorClass:errorClass,validClass:validClass,errorID:errorID,settings:settings});}},delegateEvents:function(selectors,eventFlags,element,settings){var events=[],key=0,validate=function(){settings.validate.call(this,settings);};$.each(eventFlags,function(key,value){if(value){events[key]=key;}});key=0;for(key in events){if(events.hasOwnProperty(key)){$(element).delegate(selectors,events[key]+'.h5Validate',validate);}}
return element;},bindDelegation:function(settings){$.each(patternLibrary,function(key,value){var pattern=value.toString();pattern=pattern.substring(1,pattern.length-1);$('.'+settings.classPrefix+key).attr('pattern',pattern);});$(this).filter('form').attr('novalidate','novalidate');$(this).find('form').attr('novalidate','novalidate');$(this).parents('form').attr('novalidate','novalidate');return this.each(function(){var kbEvents={focusout:settings.focusout,focusin:settings.focusin,change:settings.change,keyup:settings.keyup},mEvents={click:settings.click},activeEvents={keyup:settings.activeKeyup};settings.delegateEvents(settings.kbSelectors,kbEvents,this,settings);settings.delegateEvents(settings.mSelectors,mEvents,this,settings);settings.delegateEvents(settings.activeClassSelector,activeEvents,this,settings);});}};$.h5Validate={addPatterns:function(patterns){var patternLibrary=defaults.patternLibrary,key;for(key in patterns){if(patterns.hasOwnProperty(key)){patternLibrary[key]=patterns[key];}}
return patternLibrary;},validValues:function(selector,values){var i=0,ln=values.length,pattern='',re;for(i=0;i<ln;i++){pattern=pattern?pattern+'|'+values[i]:values[i];}
re=new RegExp('^(?:'+pattern+')$');$(selector).data('regex',re);}};$.fn.h5Validate=function(options){var settings=$.extend({},defaults,options,methods),activeClass=settings.classPrefix+settings.activeClass,action,args;$.extend(settings,{activeClass:activeClass,activeClassSelector:'.'+activeClass,requiredClass:settings.classPrefix+settings.requiredClass});settings.messages=messages;$.extend($.fn.h5Validate,h5);if(typeof options==='string'&&typeof methods[options]==='function'){args=$.makeArray(arguments);action=options;args.shift();args=$.merge(args,[settings]);return settings[action].apply(this,args);}
return methods.bindDelegation.call(this,settings);};}(jQuery));