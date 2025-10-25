/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import "./bootstrap";

/**
 * We will create a fresh Vue application instance.
 */
// import { createApp } from "vue";
import { createApp } from "vue/dist/vue.esm-bundler.js";

const app = createApp({});

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = import.meta.globEager("./components/*.vue");
// for (const key in files) {
//     app.component(key.split("/").pop().split(".")[0], files[key].default);
// }

// Yeni bileşenler
import ChatDashboard from "./components/ChatDashboard.vue";
import ChatWindow from "./components/ChatWindow.vue";
import CreateGroupModal from "./components/CreateGroupModal.vue";
import ConversationInfoModal from "./components/ConversationInfoModal.vue";
import MentionCommandInput from "./components/MentionCommandInput.vue";

// Eski bileşen (backward compatibility için)
import ChatComponent from "./components/ChatComponent.vue";

app.component("chat-dashboard", ChatDashboard);
app.component("chat-window", ChatWindow);
app.component("create-group-modal", CreateGroupModal);
app.component("conversation-info-modal", ConversationInfoModal);
app.component("mention-command-input", MentionCommandInput);
app.component("chat-component", ChatComponent);

/**
 * Next, attach Vue application instance to the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
app.mount("#app");
