<html lang="en">
<head>

    <meta charset="UTF-8">
    <title>Match Lobby</title>

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <style type="text/css">
        .vm-backdrop{position:fixed;top:0;right:0;bottom:0;left:0;background-color:rgba(0,0,0,0.5)}.vm-wrapper{position:fixed;top:0;right:0;bottom:0;left:0;overflow-x:hidden;overflow-y:auto;outline:0}.vm{position:relative;margin:0px auto;width:calc(100% - 20px);min-width:110px;max-width:500px;background-color:#fff;top:30px;cursor:default;box-shadow:0 5px 15px rgba(0,0,0,0.5)}.vm-titlebar{padding:10px 15px 10px 15px;overflow:auto;border-bottom:1px solid #e5e5e5}.vm-title{margin-top:2px;margin-bottom:0px;display:inline-block;font-size:18px;font-weight:normal}.vm-btn-close{color:#ccc;padding:0px;cursor:pointer;background:0 0;border:0;float:right;font-size:24px;line-height:1em}.vm-btn-close:before{content:'×';font-family:Arial}.vm-btn-close:hover,.vm-btn-close:focus,.vm-btn-close:focus:hover{color:#bbb;border-color:transparent;background-color:transparent}.vm-content{padding:10px 15px 15px 15px}.vm-content .full-hr{width:auto;border:0;border-top:1px solid #e5e5e5;margin-top:15px;margin-bottom:15px;margin-left:-14px;margin-right:-14px}.vm-fadeIn{animation-name:vm-fadeIn}@keyframes vm-fadeIn{0%{opacity:0}100%{opacity:1}}.vm-fadeOut{animation-name:vm-fadeOut}@keyframes vm-fadeOut{0%{opacity:1}100%{opacity:0}}.vm-fadeIn,.vm-fadeOut{animation-duration:0.25s;animation-fill-mode:both}

        * {
            box-sizing: border-box;
        }

        html {
            font-weight: 300;
            -webkit-font-smoothing: antialiased;
        }

        html, input {
            font-family: "HelveticaNeue-Light",
            "Helvetica Neue Light",
            "Helvetica Neue",
            Helvetica,
            Arial,
            "Lucida Grande",
            sans-serif;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        ul {
            list-style: none;
            word-wrap: break-word;
        }

        /* Pages */

        .pages {
            height: 100%;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .page {
            height: 100%;
            position: absolute;
            width: 100%;
        }

        /* Login Page */

        .login.page {
            background-color: #000;
        }

        .login.page .form {
            height: 100px;
            margin-top: -100px;
            position: absolute;

            text-align: center;
            top: 50%;
            width: 100%;
        }

        .login.page .form .usernameInput {
            background-color: transparent;
            border: none;
            border-bottom: 2px solid #fff;
            outline: none;
            padding-bottom: 15px;
            text-align: center;
            width: 400px;
        }

        .login.page .title {
            font-size: 200%;
        }

        .login.page .usernameInput {
            font-size: 200%;
            letter-spacing: 3px;
        }

        .login.page .title, .login.page .usernameInput {
            color: #fff;
            font-weight: 100;
        }

        /* Chat page */

        .chat.page {
            display: none;
        }

        /* Font */

        .messages {
            font-size: 150%;
        }

        .inputMessage {
            font-size: 100%;
        }

        .log {
            color: gray;
            font-size: 70%;
            margin: 5px;
            text-align: center;
        }

        /* Messages */

        .chatArea {
            height: 100%;
            padding-bottom: 60px;
            padding-top: 60px;
        }

        .messages {
            height: 100%;
            margin: 0;
            overflow-y: scroll;
            padding: 10px 20px 10px 20px;
        }

        .message.typing .messageBody {
            color: gray;
        }

        .username {
            font-weight: 700;
            overflow: hidden;
            padding-right: 15px;
            text-align: right;
        }

        /* Input */

        .inputMessage {
            border: 10px solid #000;
            bottom: 0;
            height: 60px;
            left: 0;
            outline: none;
            padding-left: 10px;
            position: absolute;
            right: 0;
            width: 100%;
        }

        .inputField {
            border: 5px solid #000;
            display: block;
            height: 50px;
            left: 30%;
            width: 35%;
            margin-top: 25px;
            position: relative;
        }

        .inputButton {
            display: block;
            height: 50px;
            left: 30%;
            width: 35%;
            margin-top: 25px;
            position: relative;
        }

        .avatar {
            vertical-align: middle;
            width: 50px;
            height: 50px;
            border-radius: 50%;
        }

        .leftTextCell {
            text-align: right;
        }

        .rightTextCell {
            text-align: left;
        }

        .full-width {
            width: 100%;
        }

        .no-stretch {
            width: 1%;
            white-space: nowrap;
        }

        .nameCell {
            font-size: 12px;
            color: #7979b7;
        }

        .imageCell {
            width: 50px;
        }

        .emptyCell {
            width: 20%;
        }

        .logoutBar {
            position: absolute;
            height: 60px;
            top: 0;
            left: 0;
            width: 100%;
        }

        .logoutButton {
            position: relative;;
            height: 50px;
            width: 100px;
            top: 5px;
            right: 50px;
            float: right;
            margin-left: 5px;
            margin-right: 5px;
        }

        .actionButton {
            position: relative;;
            height: 50px;
            width: 150px;
            top: 5px;
            right: 50px;
            float: right;
            margin-left: 5px;
            margin-right: 5px;
        }

        .submitButton {
            height: 50px;
            width: 150px;
            margin-left: 5px;
            margin-right: 5px;
        }

        .green {
            color: limegreen;
        }

        .red {
            color: indianred;
        }

        .grey {
            color: darkgrey;
        }

        .dispute {
            background: pink;
        }
    </style>
</head>

<body>
<div id="lobby">
    <div id="logout" class="logoutBar" style="display:none">
        <button class="logoutButton" v-on:click="leave"><label>Leave</label></button>
        <button class="actionButton" v-on:click="showDisputeModal=true"><label>Create Dispute</label></button>
        <button class="actionButton" v-on:click="showCoinTossModal=true"><label>Toss a Coin</label></button>
    </div>
    <div id="chat" class="chatArea" style="display:none">
        <ul id="messages" class="messages">
            <li v-for="(message, index) in messages" :class="`item-${index}`">
                <table class="full-width">
                    <template v-if="selfId != message.user.id">
                        <tr class="row">
                            <td class="imageCell cell" rowspan="3"><img
                                    v-show="index == 0 || message.user.id != messages[index - 1].user.id" class="avatar"
                                    :src="message.user.avatar"></td>
                            <td class="nameCell cell"
                                v-if="index == 0 || message.user.id != messages[index - 1].user.id">@{{ message.user.username }}
                            </td>
                            <td class="nameCell cell" v-else>&nbsp</td>
                            <td class="emptyCell"></td>
                        </tr>
                        <tr>
                            <td class="rightTextCell cell">
                                <span v-if="message.type == 'dispute'" class="dispute">Dispute by @{{ message.user.username }}: </span>
                                @{{ message.text }}
                                <span v-if="message.type == 'coin_toss' && message.status == 'accepted'" class='green'> Winner: @{{ message.winner.username }}</span>
                                <span v-if="message.type == 'coin_toss' && message.status == 'accepted'" class='red'> Loser: @{{ message.loser.username }}</span>
                                <span
                                    v-if="message.type == 'coin_toss' && message.status == 'rejected'" class='grey'> (Declined)</span>
                                <a v-if="message.type == 'dispute' && message.screenshot" :href="message.screenshot">
                                    (Screenshot) </a>
                            </td>
                            <td class="emptyCell"></td>
                        </tr>
                        <tr v-if="message.type == 'coin_toss' && message.status == 'pending'">
                            <td>
                                <button class="" v-on:click="call(message.actions[0].action)"><label>@{{
                                        message.actions[0].title }}</label></button>
                                <button class="" v-on:click="call(message.actions[1].action)"><label>@{{
                                        message.actions[1].title }}</label></button>
                            </td>
                        </tr>
                    </template>
                    <template v-else>
                        <tr class="row">
                            <td class="emptyCell"></td>
                            <td class="nameCell cell leftTextCell"
                                v-if="index == 0 || message.user.id != messages[index - 1].user.id">
                                @{{ message.user.username }}
                            </td>
                            <td class="nameCell cell leftTextCell" v-else>&nbsp;</td>
                            <td class="imageCell cell" rowspan="2"><img
                                    v-show="index == 0 || message.user.id != messages[index - 1].user.id" class="avatar"
                                    :src="message.user.avatar"></td>
                        </tr>
                        <tr>
                            <td class="emptyCell"></td>
                            <td class="leftTextCell cell">
                                <span v-if="message.type == 'dispute'" class="dispute">Dispute by @{{ message.user.username }}: </span>
                                @{{ message.text }}
                                <span v-if="message.type == 'coin_toss' && !message.is_final">(WAITING)</span>
                                <span v-if="message.type == 'coin_toss' && message.status == 'accepted'" class='green'> Winner: @{{ message.winner.username }}</span>
                                <span v-if="message.type == 'coin_toss' && message.status == 'accepted'" class='red'> Loser: @{{ message.loser.username }}</span>
                                <span
                                    v-if="message.type == 'coin_toss' && message.status == 'rejected'" class='grey'> (Declined)</span>
                                <a v-if="message.type == 'dispute' && message.screenshot" :href="message.screenshot">
                                    (Screenshot) </a>
                            </td>
                        </tr>
                    </template>
                </table>

            </li>
        </ul>

        <template>
            <Modal v-model="showDisputeModal" title="Create a New Dispute">
                <form id="disputeForm">
                    <textarea class="" style="width: 100%; margin-bottom: 10px;" rows="4" cols="50" placeholder="Dispute explanation" v-model="disputeText"></textarea>
                    <label style="margin-bottom: 10px;">Screenshot:
                        <input type="file">
                    </label>
                    <br/>
                    <button class="submitButton" style="margin-top: 30px;" v-on:click="createDispute"><label>Create Dispute</label></button>
                </form>
            </Modal>
        </template>

        <template>
            <Modal v-model="showCoinTossModal" title="Request a Coin Toss">
                <form id="coinTossForm">
                    <textarea class="" style="width: 100%; margin-bottom: 10px;" rows="4" cols="50" placeholder="Specify a reason" v-model="coinTossText"></textarea>

                    <button class="submitButton" style="margin-top: 30px;" v-on:click="createCoinTossRequest"><label>Request Coin Toss</label></button>
                </form>
            </Modal>
        </template>

        <form v-on:submit="send">
            <input class="inputMessage" placeholder="Type here..." v-model="message">

        </form>

    </div>

    <div id="login" style="display:block">
        <form v-on:submit="login">
            <input class="inputField" placeholder="Email" v-model="email">
            <input class="inputField" placeholder="Password" type="password" v-model="password">
            <input class="inputButton" type="submit" value="Login">
        </form>
    </div>

</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/3.1.2/socket.io.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
<script>
    !function(e,t){"object"==typeof exports&&"undefined"!=typeof module?module.exports=t(require("vue")):"function"==typeof define&&define.amd?define(["vue"],t):(e="undefined"!=typeof globalThis?globalThis:e||self).VueModal=t(e.Vue)}(this,(function(e){"use strict";function t(e){return e&&"object"==typeof e&&"default"in e?e:{default:e}}for(var n=t(e),o="-_",s=36;s--;)o+=s.toString(36);for(s=36;s---10;)o+=s.toString(36).toUpperCase();function i(e){return(i="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}var a={selector:"vue-portal-target-".concat(function(e){var t="";for(s=e||21;s--;)t+=o[64*Math.random()|0];return t}())},r=function(e){return a.selector=e},l="undefined"!=typeof window&&void 0!==("undefined"==typeof document?"undefined":i(document)),d=n.default.extend({abstract:!0,name:"PortalOutlet",props:["nodes","tag"],data:function(e){return{updatedNodes:e.nodes}},render:function(e){var t=this.updatedNodes&&this.updatedNodes();return t?t.length<2&&!t[0].text?t:e(this.tag||"DIV",t):e()},destroyed:function(){var e=this.$el;e.parentNode.removeChild(e)}}),u=n.default.extend({name:"VueSimplePortal",props:{disabled:{type:Boolean},prepend:{type:Boolean},selector:{type:String,default:function(){return"#".concat(a.selector)}},tag:{type:String,default:"DIV"}},render:function(e){if(this.disabled){var t=this.$scopedSlots&&this.$scopedSlots.default();return t?t.length<2&&!t[0].text?t:e(this.tag,t):e()}return e()},created:function(){this.getTargetEl()||this.insertTargetEl()},updated:function(){var e=this;this.$nextTick((function(){e.disabled||e.slotFn===e.$scopedSlots.default||(e.container.updatedNodes=e.$scopedSlots.default),e.slotFn=e.$scopedSlots.default}))},beforeDestroy:function(){this.unmount()},watch:{disabled:{immediate:!0,handler:function(e){e?this.unmount():this.$nextTick(this.mount)}}},methods:{getTargetEl:function(){if(l)return document.querySelector(this.selector)},insertTargetEl:function(){if(l){var e=document.querySelector("body"),t=document.createElement(this.tag);t.id=this.selector.substring(1),e.appendChild(t)}},mount:function(){var e=this.getTargetEl(),t=document.createElement("DIV");this.prepend&&e.firstChild?e.insertBefore(t,e.firstChild):e.appendChild(t),this.container=new d({el:t,parent:this,propsData:{tag:this.tag,nodes:this.$scopedSlots.default}})},unmount:function(){this.container&&(this.container.$destroy(),delete this.container)}}});"undefined"!=typeof window&&window.Vue&&window.Vue===n.default&&n.default.use((function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};e.component(t.name||"portal",u),t.defaultSelector&&r(t.defaultSelector)}));var c={type:[String,Object,Array],default:""},f='a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), [tabindex]:not([tabindex="-1"])',p=0;function h(e,t,n,o,s,i,a,r,l,d){"boolean"!=typeof a&&(l=r,r=a,a=!1);var u,c="function"==typeof n?n.options:n;if(e&&e.render&&(c.render=e.render,c.staticRenderFns=e.staticRenderFns,c._compiled=!0,s&&(c.functional=!0)),o&&(c._scopeId=o),i?(u=function(e){(e=e||this.$vnode&&this.$vnode.ssrContext||this.parent&&this.parent.$vnode&&this.parent.$vnode.ssrContext)||"undefined"==typeof __VUE_SSR_CONTEXT__||(e=__VUE_SSR_CONTEXT__),t&&t.call(this,l(e)),e&&e._registeredComponents&&e._registeredComponents.add(i)},c._ssrRegister=u):t&&(u=a?function(e){t.call(this,d(e,this.$root.$options.shadowRoot))}:function(e){t.call(this,r(e))}),u)if(c.functional){var f=c.render;c.render=function(e,t){return u.call(t),f(e,t)}}else{var p=c.beforeCreate;c.beforeCreate=p?[].concat(p,u):[u]}return n}var m={name:"VueModal",components:{Portal:u},model:{prop:"basedOn",event:"close"},props:{title:{type:String,default:""},baseZindex:{type:Number,default:1051},bgClass:c,wrapperClass:c,modalClass:c,modalStyle:c,inClass:Object.assign({},c,{default:"vm-fadeIn"}),outClass:Object.assign({},c,{default:"vm-fadeOut"}),bgInClass:Object.assign({},c,{default:"vm-fadeIn"}),bgOutClass:Object.assign({},c,{default:"vm-fadeOut"}),appendTo:{type:String,default:"body"},live:{type:Boolean,default:!1},enableClose:{type:Boolean,default:!0},basedOn:{type:Boolean,default:!1}},data:function(){return{zIndex:0,id:null,show:!1,mount:!1,elToFocus:null}},created:function(){this.live&&(this.mount=!0)},mounted:function(){this.id="vm-"+this._uid,this.$watch("basedOn",(function(e){var t=this;e?(this.mount=!0,this.$nextTick((function(){t.show=!0}))):this.show=!1}),{immediate:!0})},beforeDestroy:function(){this.elToFocus=null},methods:{close:function(){!0===this.enableClose&&this.$emit("close",!1)},clickOutside:function(e){e.target===this.$refs["vm-wrapper"]&&this.close()},keydown:function(e){if(27===e.which&&this.close(),9===e.which){var t=[].slice.call(this.$refs["vm-wrapper"].querySelectorAll(f)).filter((function(e){return!!(e.offsetWidth||e.offsetHeight||e.getClientRects().length)}));e.shiftKey?e.target!==t[0]&&e.target!==this.$refs["vm-wrapper"]||(e.preventDefault(),t[t.length-1].focus()):e.target===t[t.length-1]&&(e.preventDefault(),t[0].focus())}},getAllVisibleWrappers:function(){return[].slice.call(document.querySelectorAll("[data-vm-wrapper-id]")).filter((function(e){return"none"!==e.display}))},getTopZindex:function(){return this.getAllVisibleWrappers().reduce((function(e,t){return parseInt(t.style.zIndex)>e?parseInt(t.style.zIndex):e}),0)},handleFocus:function(e){var t=e.querySelector("[autofocus]");if(t)t.focus();else{var n=e.querySelectorAll(f);n.length?n[0].focus():e.focus()}},beforeOpen:function(){this.elToFocus=document.activeElement;var e=this.getTopZindex();this.zIndex=p?p+2:0===e?this.baseZindex:e+2,p=this.zIndex,this.$emit("before-open")},opening:function(){this.$emit("opening")},afterOpen:function(){this.handleFocus(this.$refs["vm-wrapper"]),this.$emit("after-open")},beforeClose:function(){this.$emit("before-close")},closing:function(){this.$emit("closing")},afterClose:function(){var e=this;this.zIndex=0,this.live||(this.mount=!1),this.$nextTick((function(){window.requestAnimationFrame((function(){var t=e.getTopZindex();if(t>0)for(var n=e.getAllVisibleWrappers(),o=0;o<n.length;o++){var s=n[o];if(parseInt(s.style.zIndex)===t){s.contains(e.elToFocus)?e.elToFocus.focus():e.handleFocus(s);break}}else document.body.contains(e.elToFocus)&&e.elToFocus.focus();p=0,e.$emit("after-close")}))}))}}},v=function(){var e=this,t=e.$createElement,n=e._self._c||t;return e.mount?n("portal",{attrs:{selector:e.appendTo}},[n("transition",{attrs:{name:"vm-backdrop-transition","enter-active-class":e.bgInClass,"leave-active-class":e.bgOutClass}},[n("div",{directives:[{name:"show",rawName:"v-show",value:e.show,expression:"show"}],staticClass:"vm-backdrop",class:e.bgClass,style:{"z-index":e.zIndex-1},attrs:{"data-vm-backdrop-id":e.id}})]),e._v(" "),n("transition",{attrs:{name:"vm-transition","enter-active-class":e.inClass,"leave-active-class":e.outClass},on:{"before-enter":e.beforeOpen,enter:e.opening,"after-enter":e.afterOpen,"before-leave":e.beforeClose,leave:e.closing,"after-leave":e.afterClose}},[n("div",{directives:[{name:"show",rawName:"v-show",value:e.show,expression:"show"}],ref:"vm-wrapper",staticClass:"vm-wrapper",class:e.wrapperClass,style:{"z-index":e.zIndex,cursor:e.enableClose?"pointer":"default"},attrs:{"data-vm-wrapper-id":e.id,tabindex:"-1",role:"dialog","aria-label":e.title,"aria-modal":"true"},on:{click:function(t){return e.clickOutside(t)},keydown:function(t){return e.keydown(t)}}},[n("div",{ref:"vm",staticClass:"vm",class:e.modalClass,style:e.modalStyle,attrs:{"data-vm-id":e.id}},[e._t("titlebar",(function(){return[n("div",{staticClass:"vm-titlebar"},[n("h3",{staticClass:"vm-title"},[e._v("\n              "+e._s(e.title)+"\n            ")]),e._v(" "),e.enableClose?n("button",{staticClass:"vm-btn-close",attrs:{type:"button","aria-label":"Close"},on:{click:function(t){return t.preventDefault(),e.close.apply(null,arguments)}}}):e._e()])]})),e._v(" "),e._t("content",(function(){return[n("div",{staticClass:"vm-content"},[e._t("default")],2)]}))],2)])])],1):e._e()};v._withStripped=!0;return h({render:v,staticRenderFns:[]},void 0,m,void 0,!1,void 0,!1,void 0,void 0,void 0)}));

    function joinRoom(bearer, token) {
        this.password = '';
        document.getElementById("login").style.display = "none";
        document.getElementById("logout").style.display = "block";
        document.getElementById("chat").style.display = "block";
        socket.emit('chat.join', {bearer: bearer, room: token});
        console.log('chat.join', JSON.stringify({bearer: bearer, room: token}));
    }

    var socketConnectParams = {'max reconnection attempts': 150};
    socketConnectParams['reconnection'] = true;
    socketConnectParams['reconnectionDelay'] = 5000;
    socketConnectParams['reconnectionDelayMax'] = 15000;
    var bearer = null;
    var token = '<?php echo e($params['token']); ?>';
    var socket = io('<?php echo e($params['url']); ?>', socketConnectParams);
    var room = '<?php echo e($params['room']); ?>';
    socket.on('connection', socket => {
        console.log('connected');
    });

    function removeListeners() {
        socket.off('chat.new_message');
        socket.off('chat.identify');
        socket.off('chat.messages');
        socket.off('chat.joined');
        socket.off('chat.left');
        socket.off('disconnect');
        socket.off('connection');
    }

    function setupListeners(vm) {
        if (localStorage.getItem('bearer' + token)) {
            bearer = localStorage.getItem('bearer' + token);
            joinRoom(bearer, token);
        }
        socket.on('chat.new_message', function (message) {
            console.log('chat.new_message', JSON.stringify(message));
            vm.messages.push(message);
            setTimeout(function () {
                scrollToElement(vm);
            }, 200);
        });
        socket.on('chat.edit_message', function (message) {
            console.log('chat.edit_message');
            for (let i in vm.messages) {
                if (message.uuid === vm.messages[i].uuid) {
                    console.log('found it');
                    vm.messages[i] = message;
                    break;
                }
            }
            var allMessages = [];
            for (let i in vm.messages) {
                allMessages.push(vm.messages[i]);
            }
            vm.messages = allMessages;
        });
        socket.on('chat.identify', function (message) {
            console.log('chat.identify', JSON.stringify(message));
            vm.selfId = message.id;
        });
        socket.on('chat.messages', function (messages) {
            console.log('chat.messages');
            vm.messages = messages;
            setTimeout(function () {
                scrollToElement(vm, false);
            }, 500);
        });
        socket.on('chat.joined', function (message) {
            console.log('chat.joined', JSON.stringify(message));
            Toastify({
                text: message.username + ' joined the lobby!',
                duration: 5000,
                destination: "",
                newWindow: true,
                close: true,
                gravity: "top",
                position: "left",
                //backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                backgroundColor: "#5cb85c",
                avatar: message.avatar,
                stopOnFocus: true, // Prevents dismissing of toast on hover
                onClick: function () {
                } // Callback after click
            }).showToast();
            //alert(message.username);
        });
        socket.on('chat.left', function (message) {
            console.log('chat.left', JSON.stringify(message));
            Toastify({
                text: message.username + ' left the lobby!',
                duration: 5000,
                destination: "",
                newWindow: true,
                close: true,
                gravity: "top",
                position: "left",
                //backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                backgroundColor: "#292b2c",
                avatar: message.avatar,
                stopOnFocus: true, // Prevents dismissing of toast on hover
                onClick: function () {
                } // Callback after click
            }).showToast();
            //alert(message.username);
        });
        socket.on('disconnect', function () {
            console.log('disconnected');
            Toastify({
                text: 'Disconnected',
                duration: 3000,
                destination: "",
                newWindow: true,
                close: false,
                gravity: "top",
                position: "left",
                //backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                backgroundColor: "#292b2c",
                stopOnFocus: false, // Prevents dismissing of toast on hover
                onClick: function () {

                } // Callback after click
            }).showToast();
            setTimeout(function () {
                console.log('reconnecting');
                removeListeners();
                socket = io('<?php echo e($params['url']); ?>', socketConnectParams);
                socket.on('connection', socket => {
                    console.log('connected');
                    Toastify({
                        text: 'Reconnected',
                        duration: 5000,
                        destination: "",
                        newWindow: true,
                        close: false,
                        gravity: "top",
                        position: "left",
                        //backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                        backgroundColor: "#5cb85c",
                        stopOnFocus: false, // Prevents dismissing of toast on hover
                        onClick: function () {

                        } // Callback after click
                    }).showToast();
                });
                setupListeners(vm)
            }, 3000)
        });
    }

    function isElementInViewport (el) {

        // Special bonus for those using jQuery
        if (typeof jQuery === "function" && el instanceof jQuery) {
            el = el[0];
        }

        var rect = el.getBoundingClientRect();

        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && /* or $(window).height() */
            rect.right <= (window.innerWidth || document.documentElement.clientWidth) /* or $(window).width() */
        );
    }

    function scrollToElement(vm, mode = true) {
        let listIndex = vm.messages.length - 1;
        const prevEl = vm.$el.getElementsByClassName('item-' + (listIndex - 1))[0];
        if (prevEl) {
            if (! isElementInViewport(prevEl) && mode) {
                return;
            }
        }
        const el = vm.$el.getElementsByClassName('item-' + listIndex)[0];

        if (el) {
            el.scrollIntoView({behavior: 'smooth'});
        }
    };

    function showToast(text, color, duration=3000) {
        Toastify({
            text: text,
            duration: duration,
            destination: "",
            newWindow: true,
            close: true,
            gravity: "top",
            position: "left",
            //backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
            backgroundColor: color,
            stopOnFocus: true, // Prevents dismissing of toast on hover
            onClick: function () {
            } // Callback after click
        }).showToast();
    }

    Vue.component('Modal', VueModal);
    new Vue({
        el: '#lobby',
        data: {
            showDisputeModal: false,
            disputeText: '',
            showCoinTossModal: false,
            coinTossText: '',
            messages: [],
            message: '',
            email: '',
            password: '',
            selfId: 0
        },

        mounted: function () {
            var vm = this;
            setupListeners(vm);
        },

        methods: {
            send: function (e) {
                socket.emit('chat.message', {text: this.message});
                console.log('chat.message', JSON.stringify({text: this.message}));
                this.message = '';
                e.preventDefault();
            },
            login: function (e) {
                e.preventDefault();
                //axios.post('https://dev.lair.gg/login', {
                axios.post('https://dev.lair.gg/login', {
                    "grant_type": "password",
                    "client_id": "2",
                    "client_secret": "8unH9NsZBJzU6nZ9egeqRFARQMnEgQMDE7yL3hbn",
                    "username": this.email,
                    "password": this.password,
                    "scope": "*"
                })
                    .then(function (response) {
                        bearer = response.data.access_token;
                        localStorage.setItem('bearer' + token, bearer);
                        localStorage.setItem('eamil', this.email);
                        this.email = '';
                        joinRoom(bearer, token);
                    })
                    .catch(function (error) {
                        console.log(error);
                        localStorage.setItem('bearer', '');
                        alert("401");
                    });
            },
            leave: function (e) {
                socket.emit('chat.leave', this.selfId);
                console.log('chat.leave', this.selfId);
                this.message = '';
                this.messages = [];
                this.selfId = 0;
                document.getElementById("login").style.display = "block";
                document.getElementById("logout").style.display = "none";
                document.getElementById("chat").style.display = "none";
                localStorage.setItem('bearer' + token, '');
                localStorage.setItem('eamil', '');
                e.preventDefault();
            },
            call: function (url) {
                let config = {
                    headers: {
                        Authorization: 'Bearer ' + bearer,
                        Accept: 'application/json'
                    }
                };
                axios.post('https://dev.lair.gg/' + url, {}, config)
                    .then(function (response) {
                        console.log(response.data.data)
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            },
            createDispute: function (e) {
                e.preventDefault();
                var vm = this;
                var formData = new FormData();
                var form = document.querySelector('#disputeForm');
                var title = form[0].value;
                var files = form[1].files;
                if (title === '') {
                    showToast('explanation cannot be empty', 'red');
                    return;
                }
                formData.append('title', title);
                if (files.length) {
                    formData.append('screenshot', files[0]);
                }
                let config = {
                    method: 'post',
                    headers: {
                        Authorization: 'Bearer ' + bearer,
                        Accept: 'application/json'
                    },
                    url: 'https://dev.lair.gg/api/v1/lobbies/' + room + '/dispute',
                    data: formData
                };
                axios(config)
                    .then(function (response) {
                        vm.disputeText = '';
                        vm.showDisputeModal = false;
                    })
                    .catch(function (error) {
                        console.log(error);
                        alert('failed');
                    });

            },
            createCoinTossRequest: function (e) {
                e.preventDefault();
                var vm = this;
                var form = document.querySelector('#coinTossForm');
                var title = form[0].value;
                if (title === '') {
                    showToast('You must specify a reason', 'red');
                    return;
                }

                let config = {
                    method: 'post',
                    headers: {
                        Authorization: 'Bearer ' + bearer,
                        Accept: 'application/json'
                    },
                    url: 'https://dev.lair.gg/api/v1/lobbies/' + room + '/coin',
                    data: {
                        'title': title
                    }
                };
                axios(config)
                    .then(function (response) {
                        vm.coinTossText = '';
                        vm.showCoinTossModal = false;
                    })
                    .catch(function (error) {
                        console.log(error);
                        alert('failed');
                    });

            }
        }
    });
</script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

</body>
</html>
<?php /**PATH /var/www/lairgg/resources/views/lobby/sample.blade.php ENDPATH**/ ?>
