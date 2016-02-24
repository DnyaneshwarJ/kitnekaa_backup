;(function(name,context,factory){var matchMedia=context.matchMedia;if(typeof module!=='undefined'&&module.exports){module.exports=factory(matchMedia);}
else if(typeof define==='function'&&define.amd){define(function(){return(context[name]=factory(matchMedia));});}
else{context[name]=factory(matchMedia);}}('enquire',this,function(matchMedia){'use strict';function each(collection,fn){var i=0,length=collection.length,cont;for(i;i<length;i++){cont=fn(collection[i],i);if(cont===false){break;}}}
function isArray(target){return Object.prototype.toString.apply(target)==='[object Array]';}
function isFunction(target){return typeof target==='function';}
function QueryHandler(options){this.options=options;!options.deferSetup&&this.setup();}
QueryHandler.prototype={setup:function(){if(this.options.setup){this.options.setup();}
this.initialised=true;},on:function(){!this.initialised&&this.setup();this.options.match&&this.options.match();},off:function(){this.options.unmatch&&this.options.unmatch();},destroy:function(){this.options.destroy?this.options.destroy():this.off();},equals:function(target){return this.options===target||this.options.match===target;}};function MediaQuery(query,isUnconditional){this.query=query;this.isUnconditional=isUnconditional;this.handlers=[];this.mql=matchMedia(query);var self=this;this.listener=function(mql){self.mql=mql;self.assess();};this.mql.addListener(this.listener);}
MediaQuery.prototype={addHandler:function(handler){var qh=new QueryHandler(handler);this.handlers.push(qh);this.matches()&&qh.on();},removeHandler:function(handler){var handlers=this.handlers;each(handlers,function(h,i){if(h.equals(handler)){h.destroy();return!handlers.splice(i,1);}});},matches:function(){return this.mql.matches||this.isUnconditional;},clear:function(){each(this.handlers,function(handler){handler.destroy();});this.mql.removeListener(this.listener);this.handlers.length=0;},assess:function(){var action=this.matches()?'on':'off';each(this.handlers,function(handler){handler[action]();});}};function MediaQueryDispatch(){if(!matchMedia){throw new Error('matchMedia not present, legacy browsers require a polyfill');}
this.queries={};this.browserIsIncapable=!matchMedia('only all').matches;}
MediaQueryDispatch.prototype={register:function(q,options,shouldDegrade){var queries=this.queries,isUnconditional=shouldDegrade&&this.browserIsIncapable;if(!queries[q]){queries[q]=new MediaQuery(q,isUnconditional);}
if(isFunction(options)){options={match:options};}
if(!isArray(options)){options=[options];}
each(options,function(handler){queries[q].addHandler(handler);});return this;},unregister:function(q,handler){var query=this.queries[q];if(query){if(handler){query.removeHandler(handler);}
else{query.clear();delete this.queries[q];}}
return this;}};return new MediaQueryDispatch();}));