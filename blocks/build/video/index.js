(()=>{var e={942:(e,o)=>{var l;!function(){"use strict";var t={}.hasOwnProperty;function n(){for(var e="",o=0;o<arguments.length;o++){var l=arguments[o];l&&(e=r(e,a(l)))}return e}function a(e){if("string"==typeof e||"number"==typeof e)return e;if("object"!=typeof e)return"";if(Array.isArray(e))return n.apply(null,e);if(e.toString!==Object.prototype.toString&&!e.toString.toString().includes("[native code]"))return e.toString();var o="";for(var l in e)t.call(e,l)&&e[l]&&(o=r(o,l));return o}function r(e,o){return o?e?e+" "+o:e+o:e}e.exports?(n.default=n,e.exports=n):void 0===(l=function(){return n}.apply(o,[]))||(e.exports=l)}()}},o={};function l(t){var n=o[t];if(void 0!==n)return n.exports;var a=o[t]={exports:{}};return e[t](a,a.exports,l),a.exports}l.n=e=>{var o=e&&e.__esModule?()=>e.default:()=>e;return l.d(o,{a:o}),o},l.d=(e,o)=>{for(var t in o)l.o(o,t)&&!l.o(e,t)&&Object.defineProperty(e,t,{enumerable:!0,get:o[t]})},l.o=(e,o)=>Object.prototype.hasOwnProperty.call(e,o),(()=>{"use strict";const e=window.wp.blocks,o=window.React,t=window.wp.serverSideRender;var n=l.n(t),a=l(942),r=l.n(a);const i=window.wp.blob,c=window.wp.blockEditor,s=window.wp.components,d=window.wp.element,u=window.wp.compose,p=window.wp.data,g=window.wp.primitives,v=(0,o.createElement)(g.SVG,{viewBox:"0 0 24 24",xmlns:"http://www.w3.org/2000/svg"},(0,o.createElement)(g.Path,{d:"M18.7 3H5.3C4 3 3 4 3 5.3v13.4C3 20 4 21 5.3 21h13.4c1.3 0 2.3-1 2.3-2.3V5.3C21 4 20 3 18.7 3zm.8 15.7c0 .4-.4.8-.8.8H5.3c-.4 0-.8-.4-.8-.8V5.3c0-.4.4-.8.8-.8h13.4c.4 0 .8.4.8.8v13.4zM10 15l5-3-5-3v6z"})),b=window.wp.notices,m=["video"],w=["image"];(0,e.registerBlockType)("aiovg/video",{attributes:{src:{type:"string"},id:{type:"number"},poster:{type:"string"},width:{type:"number",default:aiovg_blocks.video.width},ratio:{type:"number",default:aiovg_blocks.video.ratio},autoplay:{type:"boolean",default:aiovg_blocks.video.autoplay},loop:{type:"boolean",default:aiovg_blocks.video.loop},muted:{type:"boolean",default:aiovg_blocks.video.muted},playpause:{type:"boolean",default:aiovg_blocks.video.playpause},current:{type:"boolean",default:aiovg_blocks.video.current},progress:{type:"boolean",default:aiovg_blocks.video.progress},duration:{type:"boolean",default:aiovg_blocks.video.duration},speed:{type:"boolean",default:aiovg_blocks.video.speed},quality:{type:"boolean",default:aiovg_blocks.video.quality},volume:{type:"boolean",default:aiovg_blocks.video.volume},pip:{type:"boolean",default:aiovg_blocks.video.pip},fullscreen:{type:"boolean",default:aiovg_blocks.video.fullscreen},share:{type:"boolean",default:aiovg_blocks.video.share},embed:{type:"boolean",default:aiovg_blocks.video.embed},download:{type:"boolean",default:aiovg_blocks.video.download}},edit:function e({attributes:l,className:t,setAttributes:a}){const g=(0,u.useInstanceId)(e),k=(0,d.useRef)(),E=(0,d.useRef)(),{src:h,id:y,poster:f,width:_,ratio:C,autoplay:R,loop:P,muted:T,playpause:S,current:B,progress:x,duration:N,speed:U,quality:L,volume:M,pip:q,fullscreen:I,share:O,embed:j,download:V}=l,z=!y&&(0,i.isBlobURL)(h),A=(0,p.useSelect)((e=>e(c.store).getSettings().mediaUpload),[]);function F(e){e&&e.url?a({src:e.url,id:e.id,poster:e.image?.src!==e.icon?e.image?.src:void 0}):a({src:void 0,id:void 0,poster:void 0})}function D(e){e!==h&&a({src:e,id:void 0,poster:void 0})}(0,d.useEffect)((()=>{if(z){const e=(0,i.getBlobByURL)(h);e&&A({filesList:[e],onFileChange:([e])=>F(e),onError:G,allowedTypes:m})}}),[]),(0,d.useEffect)((()=>{k.current&&k.current.load()}),[f]);const{createErrorNotice:H}=(0,p.useDispatch)(b.store);function G(e){H(e,{type:"snackbar"})}const $=r()(t,{"is-transient":z}),J=(0,c.useBlockProps)({className:$});if(!h)return(0,o.createElement)("div",{...J},(0,o.createElement)(c.MediaPlaceholder,{icon:(0,o.createElement)(c.BlockIcon,{icon:v}),onSelect:F,onSelectURL:D,accept:"video/*",allowedTypes:m,value:l,onError:G}));const K=`video-block__poster-image-description-${g}`;return(0,o.createElement)(o.Fragment,null,(0,o.createElement)(c.BlockControls,null,(0,o.createElement)(c.MediaReplaceFlow,{mediaId:y,mediaURL:h,allowedTypes:m,accept:"video/*",onSelect:F,onSelectURL:D,onError:G})),(0,o.createElement)(c.InspectorControls,null,(0,o.createElement)(s.PanelBody,{title:aiovg_blocks.i18n.general_settings},(0,o.createElement)(s.PanelRow,null,(0,o.createElement)(s.TextControl,{label:aiovg_blocks.i18n.width,help:aiovg_blocks.i18n.width_help,value:_>0?_:"",onChange:e=>a({width:isNaN(e)?0:e})})),(0,o.createElement)(s.PanelRow,null,(0,o.createElement)(s.TextControl,{label:aiovg_blocks.i18n.ratio,help:aiovg_blocks.i18n.ratio_help,value:C>0?C:"",onChange:e=>a({ratio:isNaN(e)?0:e})})),(0,o.createElement)(s.PanelRow,null,(0,o.createElement)(c.MediaUploadCheck,null,(0,o.createElement)(s.BaseControl,{className:"editor-video-poster-control"},(0,o.createElement)(s.BaseControl.VisualLabel,null,aiovg_blocks.i18n.poster_image),(0,o.createElement)(c.MediaUpload,{title:aiovg_blocks.i18n.select_image,onSelect:function(e){a({poster:e.url})},allowedTypes:w,render:({open:e})=>(0,o.createElement)(s.Button,{variant:"primary",onClick:e,ref:E,"aria-describedby":K},f?aiovg_blocks.i18n.replace_image:aiovg_blocks.i18n.select_image)}),(0,o.createElement)("p",{id:K,hidden:!0},f?sprintf("The current poster image url is %s",f):"There is no poster image currently selected"),!!f&&(0,o.createElement)(s.Button,{onClick:function(){a({poster:void 0}),E.current.focus()},variant:"tertiary"},aiovg_blocks.i18n.remove_image)))),(0,o.createElement)(s.PanelRow,null,(0,o.createElement)(s.ToggleControl,{label:aiovg_blocks.i18n.autoplay,checked:R,onChange:()=>a({autoplay:!R})})),(0,o.createElement)(s.PanelRow,null,(0,o.createElement)(s.ToggleControl,{label:aiovg_blocks.i18n.loop,checked:P,onChange:()=>a({loop:!P})})),(0,o.createElement)(s.PanelRow,null,(0,o.createElement)(s.ToggleControl,{label:aiovg_blocks.i18n.muted,checked:T,onChange:()=>a({muted:!T})}))),(0,o.createElement)(s.PanelBody,{title:aiovg_blocks.i18n.player_controls},(0,o.createElement)(s.PanelRow,null,(0,o.createElement)(s.ToggleControl,{label:aiovg_blocks.i18n.play_pause,checked:S,onChange:()=>a({playpause:!S})})),(0,o.createElement)(s.PanelRow,null,(0,o.createElement)(s.ToggleControl,{label:aiovg_blocks.i18n.current_time,checked:B,onChange:()=>a({current:!B})})),(0,o.createElement)(s.PanelRow,null,(0,o.createElement)(s.ToggleControl,{label:aiovg_blocks.i18n.progressbar,checked:x,onChange:()=>a({progress:!x})})),(0,o.createElement)(s.PanelRow,null,(0,o.createElement)(s.ToggleControl,{label:aiovg_blocks.i18n.duration,checked:N,onChange:()=>a({duration:!N})})),(0,o.createElement)(s.PanelRow,null,(0,o.createElement)(s.ToggleControl,{label:aiovg_blocks.i18n.speed,checked:U,onChange:()=>a({speed:!U})})),(0,o.createElement)(s.PanelRow,null,(0,o.createElement)(s.ToggleControl,{label:aiovg_blocks.i18n.quality,checked:L,onChange:()=>a({quality:!L})})),(0,o.createElement)(s.PanelRow,null,(0,o.createElement)(s.ToggleControl,{label:aiovg_blocks.i18n.volume,checked:M,onChange:()=>a({volume:!M})})),(0,o.createElement)(s.PanelRow,null,(0,o.createElement)(s.ToggleControl,{label:aiovg_blocks.i18n.pip,checked:q,onChange:()=>a({pip:!q})})),(0,o.createElement)(s.PanelRow,null,(0,o.createElement)(s.ToggleControl,{label:aiovg_blocks.i18n.fullscreen,checked:I,onChange:()=>a({fullscreen:!I})})),(0,o.createElement)(s.PanelRow,null,(0,o.createElement)(s.ToggleControl,{label:aiovg_blocks.i18n.share,checked:O,onChange:()=>a({share:!O})})),(0,o.createElement)(s.PanelRow,null,(0,o.createElement)(s.ToggleControl,{label:aiovg_blocks.i18n.embed,checked:j,onChange:()=>a({embed:!j})})),(0,o.createElement)(s.PanelRow,null,(0,o.createElement)(s.ToggleControl,{label:aiovg_blocks.i18n.download,checked:V,onChange:()=>a({download:!V})})))),(0,o.createElement)("div",{...J},(0,o.createElement)(s.Disabled,null,(0,o.createElement)(n(),{block:"aiovg/video",attributes:l})),z&&(0,o.createElement)(s.Spinner,null)))}})})()})();