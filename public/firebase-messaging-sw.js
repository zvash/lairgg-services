


 importScripts('https://www.gstatic.com/firebasejs/8.4.3/firebase-app.js');
 importScripts('https://www.gstatic.com/firebasejs/8.4.3/firebase-messaging.js');

 var firebaseConfig = {
                apiKey: "AIzaSyDxIauiwwyOpfD1-tQYyY4D5eiV0Atf5ck",
                authDomain: "lairgg-4dcef.firebaseapp.com",
                projectId: "lairgg-4dcef",
                storageBucket: "lairgg-4dcef.appspot.com",
                messagingSenderId: "1007213527470",
                appId: "1:1007213527470:web:839cee836a6647a356483f",
                measurementId: "G-95GLY22F04"
            };
 firebase.initializeApp(firebaseConfig);
 // Retrieve an instance of Firebase Messaging so that it can handle background
 // messages.
 const messaging = firebase.messaging();



// If you would like to customize notifications that are received in the
// background (Web app is closed or not in browser focus) then you should
// implement this optional method.
// Keep in mind that FCM will still show notification messages automatically 
// and you should use data messages for custom notifications.
// For more info see: 
// https://firebase.google.com/docs/cloud-messaging/concept-options
messaging.onBackgroundMessage(function(payload) {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  // Customize notification here
});
