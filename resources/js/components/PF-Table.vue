<!-- 元の「準備中」メッセージの場所 -->
<template>
  <div id="ytScope">
    <div class="table-wrapper">
      <form @submit.prevent="handleSubmit">
        <table>
          <colgroup>
            <col style="width:72px;">  <!-- Action -->
            <col style="width:32px;">  <!-- # -->
            <col style="width:150px;"> <!-- メニュー -->
            <col style="width:240px;"> <!-- 配信面 -->
            <col style="width:190px;"> <!-- 縦型 -->
            <col style="width:190px;"> <!-- 横型 -->
            <col style="width:160px;"> <!-- 配信期間 -->
            <col style="width:140px;"> <!-- 予算 -->
            <col style="width:160px;"> <!-- 都道府県 -->
            <col style="width:180px;"> <!-- 市町村 -->
            <col style="width:90px;">  <!-- 性別 -->
            <col style="width:110px;"> <!-- 年齢① -->
            <col style="width:110px;"> <!-- 年齢② -->
            <col style="width:110px;"> <!-- 年齢③ -->
            <col style="width:240px;"> <!-- ターゲット① -->
            <col style="width:240px;"> <!-- ターゲット② -->
            <col style="width:240px;"> <!-- ターゲット③ -->
            <col style="width:120px;"> <!-- デバイス -->
            <col style="width:450px;"> <!-- 備考 -->
          </colgroup>
          <thead>
            <tr>
              <th></th>
              <th>#</th>
              <th>メニュー</th>
              <th>配信面</th>
              <th>縦型</th>
              <th>横型</th>
              <th>配信期間</th>
              <th>予算</th>
              <th>エリア（都道府県）</th>
              <th>エリア（市町村）</th>
              <th>性別</th>
              <th>年齢①</th>
              <th>年齢②</th>
              <th>年齢③</th>
              <th>ターゲティング①</th>
              <th>ターゲティング②</th>
              <th>ターゲティング③</th>
              <th>デバイス</th>
              <th>備考</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, index) in rows" :key="index">
              <!-- アクション（複製・削除） -->
              <td>
                <div class="action-col-inner">
                  <button type="button" class="del-half-btn" @click="deleteRow(index)" title="削除">✕</button>
                  <button type="button" class="copy-half-btn" @click="copyRow(index)" title="複製">📋</button>
                </div>
              </td>
              <td style="text-align:center;">{{ index + 1 }}</td>

              <!-- メニュー & 配信面 -->
              <td>
                <select v-model="row.menu">
                  <option value="バンパー広告">バンパー広告</option>
                  <option value="スキップ可能">スキップ可能</option>
                  <option value="スキップ不可">スキップ不可</option>
                  <option value="インフィード広告">インフィード広告</option>
                  <option value="マストヘッド">マストヘッド</option>
                  <option value="ショート広告">ショート広告</option>
                </select>
              </td>
              <td><input type="text" v-model="row.placement" /></td>

              <!-- 縦型 / 横型 -->
              <td>
                <select v-model="row.verticalAspect">
                  <option v-for="opt in YOUTUBE_CONFIG.videoLengthOptions" :key="opt.value" :value="opt.value">{{ opt.text }}</option>
                </select>
              </td>
              <td>
                <select v-model="row.horizontalAspect">
                  <option v-for="opt in YOUTUBE_CONFIG.videoLengthOptions" :key="opt.value" :value="opt.value">{{ opt.text }}</option>
                </select>
              </td>

              <!-- 配信期間 -->
              <td>
                <div class="period-wrap">
                  <input type="number" class="period-number" v-model.number="row.periodNum" />
                  <select class="period-unit" v-model="row.periodUnit">
                    <option value="ヶ月">ヶ月</option>
                    <option value="日">日</option>
                  </select>
                </div>
              </td>

              <!-- 予算 (￥カンマ自動フォーマット) -->
              <td>
                <input type="text" v-model="row.budget" @blur="formatRowBudget(row)" placeholder="例：￥500,000" />
              </td>

              <!-- エリア（都道府県モーダル起動） -->
              <td>
                <div class="pref-box" @click="openPrefModal(index)">
                  <div class="pref-lines" v-if="row.prefArea.length">
                    <span v-for="p in row.prefArea" :key="p" class="pref-line">{{ p }}</span>
                  </div>
                  <span v-else class="pref-placeholder">選択してください</span>
                </div>
              </td>

              <!-- エリア（市町村 - ★ALL時は非活性） -->
              <td>
                <textarea 
                  class="city-textarea" 
                  v-model="row.cityArea" 
                  :disabled="row.prefArea.includes('★ALL')" 
                  placeholder="エリアを入力"
                ></textarea>
              </td>

              <!-- 性別 / 年齢①〜③ -->
              <td>
                <select v-model="row.gender">
                  <option v-for="g in YOUTUBE_CONFIG.genderOptions" :key="g" :value="g">{{ g }}</option>
                </select>
              </td>
              <td>
                <select v-model="row.age1" class="age-num">
                  <option v-for="a in YOUTUBE_CONFIG.ageOptions" :key="a" :value="a">{{ a }}</option>
                </select>
              </td>
              <td>
                <select v-model="row.age2" class="age-num">
                  <option value="">選択</option>
                  <option v-for="a in YOUTUBE_CONFIG.ageOptions" :key="a" :value="a">{{ a }}</option>
                </select>
              </td>
              <td>
                <select v-model="row.age3" class="age-num">
                  <option value="">選択</option>
                  <option v-for="a in YOUTUBE_CONFIG.ageOptions" :key="a" :value="a">{{ a }}</option>
                </select>
              </td>

              <!-- ターゲティング①〜③ -->
              <td v-for="tNum in [1, 2, 3]" :key="tNum">
                <div class="target-wrap">
                  <select v-model="row['target' + tNum].category" @change="openTargetModal(index, tNum)">
                    <option value="">なし</option>
                    <option value="affinity">アフィニティ</option>
                    <option value="inmarket">インマーケット</option>
                    <option value="detailedDemo">詳しい属性</option>
                    <option value="lifeEvent">ライフイベント</option>
                  </select>
                  <div class="selected-display" v-if="row['target' + tNum].selected.length">
                    <span v-for="item in row['target' + tNum].selected" :key="item">{{ item }}</span>
                  </div>
                </div>
              </td>

              <!-- デバイス -->
              <td>
                <select v-model="row.device">
                  <option v-for="d in YOUTUBE_CONFIG.deviceOptions" :key="d" :value="d">{{ d }}</option>
                </select>
              </td>

              <!-- 備考 -->
              <td>
                <textarea v-model="row.notes" rows="2" placeholder="備考・補足事項"></textarea>
              </td>
            </tr>
          </tbody>
        </table>

        <div class="actions">
          <button type="button" @click="addRow">+ New</button>
        </div>
      </form>
    </div>

    <!-- ================= モーダル：都道府県選択 ================= -->
    <div class="modal" :class="{ show: activeModal === 'pref' }">
      <div class="modal-content" style="max-width:600px;">
        <div class="modal-header">
          <h3>エリア（都道府県）を選択</h3>
          <span class="close-btn" @click="closeModal">&times;</span>
        </div>
        <div class="modal-search-bar">
          <input type="text" v-model="modalSearch" placeholder="都道府県を検索…" />
          <button type="button" @click="modalSelected = []">クリア</button>
        </div>
        <div class="modal-selected-header">選択済み</div>
        <div class="modal-selected-body">
          <span v-for="item in modalSelected" :key="item" class="pref-chip">
            {{ item }}
            <button type="button" @click="toggleSelection(item)">×</button>
          </span>
        </div>
        <div class="modal-candidates-header">候補</div>
        <div class="modal-candidates-body pref-grid">
          <label v-for="p in filteredPrefectures" :key="p" class="pref-opt">
            <input type="checkbox" :value="p" :checked="modalSelected.includes(p)" @change="toggleSelection(p)" />
            <span>{{ p }}</span>
          </label>
        </div>
        <div class="modal-footer">
          <button type="button" @click="closeModal">キャンセル</button>
          <button type="button" class="btn-primary" @click="savePrefSelection">決定</button>
        </div>
      </div>
    </div>

    <!-- ================= モーダル：ターゲティング選択 ================= -->
    <div class="modal" :class="{ show: activeModal === 'target' }">
      <div class="modal-content" style="max-width:760px;">
        <div class="modal-header">
          <h3>ターゲティングカテゴリを選択</h3>
          <span class="close-btn" @click="closeModal">&times;</span>
        </div>
        <div class="modal-search-bar">
          <input type="text" v-model="modalSearch" placeholder="キーワードで検索…" />
          <button type="button" @click="modalSelected = []">クリア</button>
        </div>
        <div class="modal-selected-header">選択済み</div>
        <div class="modal-selected-body">
          <span v-for="item in modalSelected" :key="item" class="pref-chip">
            {{ item }}
            <button type="button" @click="toggleSelection(item)">×</button>
          </span>
        </div>
        <div class="modal-candidates-header">候補</div>
        <div class="modal-candidates-body">
          <label v-for="item in filteredTargetOptions" :key="item" class="pref-opt">
            <input type="checkbox" :value="item" :checked="modalSelected.includes(item)" @change="toggleSelection(item)" />
            <span>{{ item }}</span>
          </label>
        </div>
        <div class="modal-footer">
          <button type="button" @click="closeModal">キャンセル</button>
          <button type="button" class="btn-primary" @click="saveTargetSelection">決定</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { YOUTUBE_CONFIG } from './PF-Config.js';

const rows = ref([JSON.parse(JSON.stringify(YOUTUBE_CONFIG.defaultRow))]);

// モーダル用ステート
const activeModal = ref(null); // 'pref' | 'target' | null
const currentRowIndex = ref(null);
const currentTargetNum = ref(null);
const modalSearch = ref('');
const modalSelected = ref([]);

// 行の制御メソッド
const addRow = () => {
  rows.value.push(JSON.parse(JSON.stringify(YOUTUBE_CONFIG.defaultRow)));
};

const copyRow = (index) => {
  const copied = JSON.parse(JSON.stringify(rows.value[index]));
  rows.value.splice(index + 1, 0, copied);
};

const deleteRow = (index) => {
  if (rows.value.length > 1) {
    rows.value.splice(index, 1);
  }
};

const formatRowBudget = (row) => {
  if (!row.budget) return;
  let val = row.budget.toString().replace(/[￥,]/g, "").trim();
  if (val && !isNaN(val)) {
    row.budget = "￥" + Number(val).toLocaleString();
  }
};

// モーダル共通制御
const closeModal = () => {
  activeModal.value = null;
  modalSearch.value = '';
  modalSelected.value = [];
};

const toggleSelection = (item) => {
  const idx = modalSelected.value.indexOf(item);
  if (idx > -1) {
    modalSelected.value.splice(idx, 1);
  } else {
    modalSelected.value.push(item);
  }
};

// 都道府県モーダル制御
const openPrefModal = (index) => {
  currentRowIndex.value = index;
  modalSelected.value = [...rows.value[index].prefArea];
  activeModal.value = 'pref';
};

const savePrefSelection = () => {
  const row = rows.value[currentRowIndex.value];
  row.prefArea = [...modalSelected.value];
  if (row.prefArea.includes('★ALL')) {
    row.cityArea = ''; // ALL選択時は市町村クリア
  }
  closeModal();
};

const filteredPrefectures = computed(() => {
  if (!modalSearch.value) return YOUTUBE_CONFIG.prefectureList;
  return YOUTUBE_CONFIG.prefectureList.filter(p => p.includes(modalSearch.value));
});

// ターゲティングモーダル制御
const openTargetModal = (rowIndex, targetNum) => {
  const targetObj = rows.value[rowIndex]['target' + targetNum];
  if (!targetObj.category) {
    targetObj.selected = [];
    return;
  }
  currentRowIndex.value = rowIndex;
  currentTargetNum.value = targetNum;
  modalSelected.value = [...targetObj.selected];
  activeModal.value = 'target';
};

const saveTargetSelection = () => {
  const row = rows.value[currentRowIndex.value];
  row['target' + currentTargetNum.value].selected = [...modalSelected.value];
  closeModal();
};

const filteredTargetOptions = computed(() => {
  if (!currentRowIndex.value && currentRowIndex.value !== 0) return [];
  const category = rows.value[currentRowIndex.value]['target' + currentTargetNum.value]?.category;
  const list = YOUTUBE_CONFIG.targetCategories[category] || [];
  if (!modalSearch.value) return list;
  return list.filter(item => item.toLowerCase().includes(modalSearch.value.toLowerCase()));
});
</script>

<style scoped>
#ytScope { width: 100%; overflow: visible; font-family: -apple-system, sans-serif; }
.table-wrapper {
  margin: 0 16px 24px 16px;
  background: linear-gradient(180deg, #e8e8ed 0%, #d1d1d6 100%);
  border-radius: 24px;
  overflow-x: auto;
  box-shadow: 0 20px 60px rgba(0,0,0,0.25);
}
table { border-collapse: collapse; table-layout: fixed; width: 3393px !important; }
thead { background: linear-gradient(180deg, #0a0a0a 0%, #1a1a1a 100%); color: #fff; }
th { font-size: 11px; padding: 10px 4px; border-right: 1px solid rgba(255,255,255,0.2); text-align: center; }
td { border-bottom: 1px solid rgba(0,0,0,0.08); border-right: 1px solid rgba(0,0,0,0.06); padding: 8px !important; vertical-align: middle; position: relative; }
tbody tr { background: rgba(255,255,255,0.6); }
tbody tr:hover { background: rgba(255,255,255,0.85); }

input, select, textarea, .pref-box {
  width: 100%; background: #ffffff !important; border: 1px solid rgba(0,0,0,0.15) !important;
  border-radius: 10px !important; padding: 6px 8px !important; font-size: 12px; box-sizing: border-box;
}
input:disabled, textarea:disabled { background: #f2f2f7 !important; color: rgba(0,0,0,0.3) !important; cursor: not-allowed; }
.city-textarea { height: 48px; resize: none; }

.action-col-inner { display: flex; flex-direction: row; gap: 4px; justify-content: center; }
.del-half-btn, .copy-half-btn { border: none; background: transparent; cursor: pointer; font-size: 14px; }
.del-half-btn { color: #c94b4b; }

.period-wrap { display: flex; gap: 6px; align-items: center; }
.period-number { width: 44px !important; text-align: center; }

.pref-box { min-height: 36px; cursor: pointer; }
.pref-line { display: block; font-size: 11px; }
.pref-placeholder { color: rgba(0,0,0,0.35); }

.target-wrap { display: flex; flex-direction: column; gap: 4px; }
.selected-display span { display: block; color: rgb(0,100,220); font-size: 10px; text-align: left; }

.actions { padding: 16px; }
.actions button { background: #fff; border: 1px solid #d1d1d6; border-radius: 12px; padding: 8px 16px; font-weight: 700; cursor: pointer; }

/* モーダル */
.modal { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.45); backdrop-filter: blur(8px); z-index: 99999; align-items: center; justify-content: center; }
.modal.show { display: flex; }
.modal-content { background: #fff; border-radius: 16px; padding: 24px; width: 100%; box-shadow: 0 20px 60px rgba(0,0,0,0.25); }
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
.close-btn { cursor: pointer; font-size: 20px; }
.modal-search-bar { display: flex; gap: 8px; margin-bottom: 12px; }
.modal-selected-body { min-height: 36px; border: 1px solid rgba(0,0,0,0.08); border-radius: 8px; padding: 6px; display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; background: #fafafa; }
.pref-chip { background: rgba(0,100,220,0.09); border: 1px solid rgba(0,100,220,0.22); color: rgb(0,100,220); border-radius: 20px; padding: 2px 8px; font-size: 11px; }
.pref-chip button { border: none; background: transparent; color: rgb(0,100,220); cursor: pointer; margin-left: 4px; }
.modal-candidates-body { max-height: 300px; overflow-y: auto; border: 1px solid rgba(0,0,0,0.08); border-radius: 8px; padding: 6px; }
.pref-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; }
.pref-opt { display: flex; align-items: center; gap: 6px; font-size: 12px; padding: 4px; cursor: pointer; }
.modal-footer { display: flex; justify-content: flex-end; gap: 8px; margin-top: 16px; }
.btn-primary { background: rgb(0,100,220) !important; color: #fff !important; border: none !important; font-weight: 700; }
</style>