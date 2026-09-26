import './bootstrap.js';
import { createApp } from 'vue';
import SimForm from './components/SimForm.vue';

// ルートインスタンスの作成
const app = createApp({
  components: {
    SimForm
  }
});

// <sim-form></sim-form> タグを Blade 等で使えるようにコンポーネント登録
app.component('sim-form', SimForm);

// #app 要素にマウント
app.mount('#app');