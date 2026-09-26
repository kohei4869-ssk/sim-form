<template>
  <div class="sim-form-container">
    <!-- ヘッダー（ログイン・登録画面では非表示） -->
    <header v-if="isLoggedIn" class="header">
      <div class="logo-area">
        <span class="company-name">Hakuhodo DY ONE</span>
        <span class="app-title">SIM FORM</span>
      </div>
      <nav class="stepper">
        <div class="step" :class="{ active: currentStep === 1 }">ENTRY</div>
        <div class="step" :class="{ active: currentStep === 2 }">PLATFORMS</div>
        <div class="step" :class="{ active: currentStep === 3 }">FORM</div>
      </nav>
    </header>
    <!-- メインコンテンツ領域 -->
    <main class="content-wrapper">
      <transition name="fade-slide" mode="out-in">
        <div class="transition-wrapper" key="steps-wrapper">
          
          <!-- STEP 1: ENTRY -->
          <section v-if="currentStep === 1" key="step1" class="step-panel step1-content">
            <!-- 未ログイン時：Login / Create account カード（パスワードあり。Google等の外部ログインは非対応） -->
            <div v-if="!isLoggedIn" class="auth-screen">
              <div class="auth-card">
                <div class="auth-logo-row">
                  <span class="company-name">Hakuhodo DY ONE</span>
                  <span class="app-title">SIM FORM</span>
                </div>
                <template v-if="authView === 'login'">
                  <h2 class="auth-title">Log in to your account</h2>
                  <p class="auth-subtitle unified-font">メールアドレスとパスワードを入力してください</p>
                  <div class="form-group">
                    <label class="unified-font">メールアドレス</label>
                    <input
                      type="email"
                      class="unified-font transparent-input"
                      v-model="loginForm.email"
                      placeholder="you@example.com"
                      autocomplete="username"
                      @keyup.enter="submitLogin"
                    />
                  </div>
                  <div class="form-group">
                    <label class="unified-font">パスワード</label>
                    <input
                      type="password"
                      class="unified-font transparent-input"
                      v-model="loginForm.password"
                      placeholder="パスワードを入力"
                      autocomplete="current-password"
                      @keyup.enter="submitLogin"
                    />
                  </div>
                  <p v-if="authError" class="error-text">{{ authError }}</p>
                  <button type="button" class="next-btn animated-btn visible auth-submit-btn" :disabled="authLoading" @click="submitLogin">
                    <span>{{ authLoading ? 'Logging in...' : 'Log in' }}</span>
                  </button>
                  <p class="auth-switch-text unified-font">アカウントをお持ちでない方は
                    <button type="button" class="auth-switch-link" @click="switchAuthView('register')">Create an account</button>
                  </p>
                </template>
                <template v-else>
                  <h2 class="auth-title">Create an account</h2>
                  <div class="auth-name-row">
                    <div class="form-group">
                      <label class="unified-font">姓</label>
                      <input
                        type="text"
                        class="unified-font transparent-input"
                        v-model="registerForm.lastName"
                        placeholder="山田"
                        autocomplete="family-name"
                        @keyup.enter="submitRegister"
                      />
                    </div>
                    <div class="form-group">
                      <label class="unified-font">名</label>
                      <input
                        type="text"
                        class="unified-font transparent-input"
                        v-model="registerForm.firstName"
                        placeholder="太郎"
                        autocomplete="given-name"
                        @keyup.enter="submitRegister"
                      />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="unified-font">メールアドレス</label>
                    <input
                      type="email"
                      class="unified-font transparent-input"
                      v-model="registerForm.email"
                      placeholder="you@example.com"
                      autocomplete="username"
                      @keyup.enter="submitRegister"
                    />
                  </div>
                  <div class="form-group">
                    <label class="unified-font">パスワード</label>
                    <input
                      type="password"
                      class="unified-font transparent-input"
                      v-model="registerForm.password"
                      placeholder="8文字以上で入力してください"
                      autocomplete="new-password"
                      @keyup.enter="submitRegister"
                    />
                  </div>
                  <p v-if="authError" class="error-text">{{ authError }}</p>
                  <button type="button" class="next-btn animated-btn visible auth-submit-btn" :disabled="authLoading" @click="submitRegister">
                    <span>{{ authLoading ? 'Creating...' : 'Create account' }}</span>
                  </button>
                  <p class="auth-switch-text unified-font">すでにアカウントをお持ちの方は
                    <button type="button" class="auth-switch-link" @click="switchAuthView('login')">Log in</button>
                  </p>
                </template>
              </div>
            </div>
            <!-- ログイン済み：通常のENTRYフォーム -->
            <template v-else>
            <div class="title-area">
              <h2>ENTRY</h2>
              <span class="required-label">*必須入力</span>
            </div>
            <p class="logged-in-as unified-font">Logged in as <strong>{{ loggedInUser.name }}</strong> （{{ loggedInUser.email }}） <button type="button" class="auth-switch-link" @click="logout">Log out</button></p>
            <div class="form-body-vertical">
              <!-- ❶ 所属 -->
              <div class="form-group" :class="{ 'is-dimmed': isFieldDimmed(1, formData.department) }">
                <label class="unified-font"><span class="num">1</span> <span class="req">*</span>所属</label>
                <div class="radio-row">
                  <label
                    v-for="option in departmentOptions"
                    :key="option.value"
                    class="radio-item"
                    :class="{ 'is-selected': formData.department === option.value }"
                  >
                    <input
                      type="radio"
                      name="department"
                      :value="option.value"
                      v-model="formData.department"
                      @focus="activeFieldIndex = 1"
                    />
                    <span class="custom-radio"></span>
                    <span class="radio-label-text unified-font">{{ option.label }}</span>
                  </label>
                </div>
              </div>
              <!-- ❷ クライアント名 -->
              <div class="form-group" :class="{ 'is-dimmed': isFieldDimmed(2, formData.clientName) }">
                <label class="unified-font"><span class="num">2</span> <span class="req">*</span>クライアント名</label>
                <input
                  type="text"
                  ref="applicantNameInput"
                  class="unified-font input-ime-active transparent-input"
                  v-model="formData.clientName"
                  @focus="activeFieldIndex = 2"
                  placeholder="例：株式会社Hakuhodo DY ONE"
                />
              </div>
              <!-- ❸ 案件名 -->
              <div class="form-group" :class="{ 'is-dimmed': isFieldDimmed(3, formData.projectName) }">
                <label class="unified-font"><span class="num">3</span> <span class="req">*</span>案件名</label>
                <input
                  type="text"
                  class="unified-font input-ime-active transparent-input"
                  v-model="formData.projectName"
                  @focus="activeFieldIndex = 3"
                  placeholder="入力してください"
                />
              </div>
              <!-- ❹ 希望納期 -->
              <div class="form-group" :class="{ 'is-dimmed': isFieldDimmed(4, formData.dueDate) }">
                <label class="unified-font"><span class="num">4</span> 希望納期</label>
                <input
                  type="date"
                  class="unified-font custom-date-input transparent-input"
                  v-model="formData.dueDate"
                  :min="todayDateString"
                  @focus="activeFieldIndex = 4"
                />
              </div>
              <!-- ❺ 備考 -->
              <div class="form-group" :class="{ 'is-dimmed': isFieldDimmed(5, formData.remarks) }">
                <label class="unified-font"><span class="num">5</span> 備考</label>
                <textarea
                  class="unified-font custom-textarea input-ime-active transparent-input"
                  v-model="formData.remarks"
                  @focus="activeFieldIndex = 5"
                  placeholder="補足事項があれば入力してください"
                ></textarea>
              </div>
            </div>
            <div class="action-area">
              <button
                class="next-btn animated-btn"
                :class="{ visible: isStep1Valid }"
                :disabled="!isStep1Valid"
                @click="goToPlatformsStep"
              >
                <span>PLATFORMS 選択へ進む</span>
                <span class="btn-arrow">&rarr;</span>
              </button>
            </div>
            </template>
          </section>
          <!-- STEP 2: PLATFORMS -->
          <section v-else-if="currentStep === 2" key="step2" class="step-panel step2-content">
            <div class="platform-header">
              <h2>PLATFORMS</h2>
              <span class="sub-label">依頼するプラットフォームを選択し、マージンを設定してください</span>
            </div>
            <div class="platform-grid-container">
              <div class="platform-label-column">
                <div class="label-header">媒体</div>
                <div class="label-cell">マージン</div>
                <div class="label-cell stripe">マージン率</div>
              </div>
              <div class="platform-columns-wrapper">
                <div
                  v-for="platform in platformList"
                  :key="platform.id"
                  class="platform-column"
                >
                  <div class="chip-cell">
                    <div
                      class="platform-chip"
                      :class="{ 'selected': platforms[platform.id].selected, 'is-coming-soon': platform.comingSoon }"
                      @click="togglePlatform(platform.id)"
                      @mouseenter="onMouseEnter(platform.id)"
                      @mouseleave="onMouseLeave(platform.id)"
                    >
                      <div class="chip-logos">
                        <div
                          v-for="(logo, idx) in platform.logos"
                          :key="idx"
                          class="lottie-wrapper"
                          :class="[logo.class, { 'is-active': hoveredPlatform === platform.id || platforms[platform.id].selected }]"
                        >
                          <DotLottieVue
                            v-if="logo.lottiePath"
                            :src="logo.lottiePath"
                            :loop="true"
                            :ref="(el) => setLottieRef(el, platform.id)"
                            class="lottie-player"
                          />
                          <img
                            v-else-if="logo.imagePath"
                            :src="logo.imagePath"
                            :alt="platform.name"
                            class="lottie-player static-logo-image"
                          />
                        </div>
                      </div>
                      <span class="platform-chip-name">{{ platform.name }}</span>
                    </div>
                  </div>
                  <div class="grid-cell">
                    <select
                      v-if="!platform.comingSoon"
                      v-model="platforms[platform.id].marginKind"
                      class="margin-select-line"
                      :disabled="!platforms[platform.id].selected"
                    >
                      <option value="normal">通常</option>
                      <option value="irregular">イレギュラー</option>
                    </select>
                    <span v-else class="coming-soon-badge">Coming soon...</span>
                  </div>
                  <div class="grid-cell stripe yg-margin-stripe">
                    <template v-if="!platform.comingSoon">
                      <template v-if="platforms[platform.id].marginKind === 'normal'">
                        <div class="yg-margin-rate-line">{{ getPlatformNormalRate(platform.id) }}%</div>
                        <div class="yg-margin-formula-line">{{ platformMarginFormulaText(platform.id) }}</div>
                      </template>
                      <template v-else>
                        <div class="yg-margin-rate-line">{{ platformTotalMarginRate(platform.id) }}%</div>
                        <div class="yg-margin-breakdown-line">
                          <span class="yg-margin-breakdown-branch">└</span>
                          <span class="yg-margin-breakdown-label">BA</span>
                          <input
                            type="number"
                            v-model.number="platforms[platform.id].baRate"
                            class="margin-input-line transparent-input yg-margin-breakdown-input"
                            :disabled="!platforms[platform.id].selected"
                          />
                          <span class="margin-suffix-text">%</span>
                        </div>
                        <div class="yg-margin-breakdown-line">
                          <span class="yg-margin-breakdown-branch">└</span>
                          <span class="yg-margin-breakdown-label">ONE</span>
                          <input
                            type="number"
                            v-model.number="platforms[platform.id].oneRate"
                            class="margin-input-line transparent-input yg-margin-breakdown-input"
                            :disabled="!platforms[platform.id].selected"
                          />
                          <span class="margin-suffix-text">%</span>
                        </div>
                        <div class="yg-margin-formula-line">{{ platformMarginFormulaText(platform.id) }}</div>
                      </template>
                    </template>
                  </div>
                </div>
              </div>
            </div>
            <div class="action-area step2-actions">
              <button class="back-btn" @click="currentStep = 1">&larr; ENTRYに戻る</button>
              <button
                class="next-btn animated-btn visible"
                :disabled="!isStep2Valid"
                @click="goToFormStep"
              >
                <span>FORM 入力へ進む</span>
                <span class="btn-arrow">&rarr;</span>
              </button>
            </div>
          </section>
          <!-- STEP 3: FORM DETAILS -->
          <section v-else-if="currentStep === 3" key="step3" class="step-panel step3-content">
            <div class="platform-header">
              <h2>DETAIL - FORM</h2>
              <span class="sub-label">選択したプラットフォームの詳細パラメータを設定してください</span>
            </div>
            <div class="platform-tabs" v-if="selectedPlatformObjects.length > 0">
              <button
                v-for="platform in selectedPlatformObjects"
                :key="platform.id"
                :ref="(el) => setTabButtonRef(el, platform.id)"
                type="button"
                :class="['material-tab', 'tab-btn-' + platform.id, { active: activePlatformTab === platform.name }]"
                @click="activePlatformTab = platform.name"
              >
                {{ capitalizeLabel(platform.name) }}
              </button>
              <span
                class="tab-underline-indicator"
                :class="{ 'tab-underline-youtube': activePlatformTab === 'YouTube' || activePlatformTab === 'YOUTUBE' }"
                :style="{ left: tabIndicatorStyle.left, width: tabIndicatorStyle.width, opacity: tabIndicatorStyle.opacity }"
              ></span>
            </div>
            <div class="platform-content">
              <transition name="tab-content-fade" mode="out-in">
              <div :key="activePlatformTab">
              <!-- YouTubeタブの場合 -->
              <div v-if="activePlatformTab === 'YouTube' || activePlatformTab === 'youtube' || activePlatformTab === 'YOUTUBE'" class="youtube-form-container">
                <div class="dark-card-wrapper">
                  <div class="table-scroll-container">
                    <table class="pattern-matrix-table" style="table-layout: fixed; min-width: 1758px; width: 100%;">
  <colgroup>
    <col style="width:48px;"><!-- 操作(削除/複製) -->
    <col style="width:30px;"><!-- # -->
    <col style="width:150px;"><!-- メニュー -->
    <col style="width:150px;"><!-- 配信面 -->
    <col style="width:140px;"><!-- 広告素材 -->
    <col style="width:60px;"><!-- デバイス -->
    <col style="width:80px;"><!-- 配信期間 -->
    <col style="width:100px;"><!-- 予算 -->
    <col style="width:170px;"><!-- エリア -->
    <col style="width:70px;"><!-- 性別 -->
    <col style="width:90px;"><!-- 年齢 -->
    <col style="width:140px;"><!-- ターゲティング① -->
    <col style="width:140px;"><!-- ターゲティング② -->
    <col style="width:140px;"><!-- ターゲティング③ -->
    <col style="width:200px;"><!-- 備考 -->
  </colgroup>
  <thead>
    <tr>
      <th>操作</th><th>#</th><th>メニュー</th><th>配信面</th><th>広告素材</th><th>デバイス</th>
      <th>配信期間</th><th>予算</th><th>エリア</th><th>性別</th><th>年齢</th>
      <th>ターゲティング①</th><th>ターゲティング②</th><th>ターゲティング③</th><th>備考</th>
    </tr>
  </thead>
  <tbody>
    <tr v-for="(pattern, index) in youtubePatterns" :key="pattern.id">
      
<!-- 【2】操作列 1列目 (上: 削除 / 下: 複製) -->
      <!-- セル自体を上下2分割のボタンにして、クリック領域を最大化します -->
      <td class="row-action-cell">
        <div class="row-action-stack">
          <!-- 上半分：削除ボタン -->
          <button type="button"
                  :disabled="youtubePatterns.length <= 1"
                  @click="removeYoutubePattern(index)"
                  title="削除"
                  class="row-action-btn row-action-delete">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>
          </button>
          <!-- 下半分：複製ボタン -->
          <button type="button"
                  @click="copyYoutubePattern(index)"
                  title="複製"
                  class="row-action-btn row-action-copy">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M6 15H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v1"/></svg>
          </button>
        </div>
      </td>
      <td class="text-center font-bold">{{ index + 1 }}</td>
      <td class="overview-cell" :class="{ 'is-empty': !pattern.menu }" :title="pattern.menu || '未設定'" @click="openEditModal(pattern, index)">
        <span v-if="pattern.menu" class="notion-tag" :class="'notion-tag-' + getMenuColor(pattern.menu)">{{ pattern.menu }}</span>
        <span v-else>未設定</span>
      </td>
      <td class="tag-cell" :class="{ 'is-empty': !pattern.placement.length }" :title="pattern.placement.join(' / ') || '未設定'" @click="openEditModal(pattern, index)">
        <div class="tag-cell-inner">
          <template v-if="pattern.placement.length">
            <span v-for="v in pattern.placement" :key="v" class="notion-tag notion-tag-gray">{{ v }}</span>
          </template>
          <span v-else>未設定</span>
        </div>
      </td>
      <td class="overview-cell" :class="{ 'is-empty': !pattern.verticalCreative && !pattern.horizontalCreative }" :title="formatCreative(pattern)" @click="openEditModal(pattern, index)">{{ formatCreative(pattern) }}</td>
      <td class="overview-cell" @click="openEditModal(pattern, index)">{{ formatDeviceLabel(pattern) }}</td>
      <td class="overview-cell" :class="{ 'is-empty': !pattern.periodNumber }" @click="openEditModal(pattern, index)">{{ formatPeriod(pattern) }}</td>
      <td class="overview-cell" :class="{ 'is-empty': !pattern.budget }" @click="openEditModal(pattern, index)">{{ formatBudget(pattern) }}</td>
      <td class="tag-cell" :class="{ 'is-empty': !pattern.prefNames.length }" :title="formatArea(pattern)" @click="openEditModal(pattern, index)">
        <div class="tag-cell-inner">
          <template v-if="pattern.prefNames.length">
            <span v-for="name in pattern.prefNames" :key="name" class="notion-tag notion-tag-gray">{{ name }}</span>
            <span v-if="pattern.city" class="tag-cell-extra">・{{ pattern.city }}</span>
          </template>
          <span v-else>未設定</span>
        </div>
      </td>
      <td class="overview-cell" :title="formatGenderOnly(pattern)" @click="openEditModal(pattern, index)">{{ formatGenderOnly(pattern) }}</td>
      <td class="overview-cell" :title="formatAgeOnly(pattern)" @click="openEditModal(pattern, index)">{{ formatAgeOnly(pattern) }}</td>
      <td class="overview-cell" :class="{ 'is-empty': pattern.targeting1Type === 'なし' }" :title="formatTarget(pattern, 1)" @click="openEditModal(pattern, index)">{{ formatTarget(pattern, 1) }}</td>
      <td class="overview-cell" :class="{ 'is-empty': pattern.targeting2Type === 'なし' }" :title="formatTarget(pattern, 2)" @click="openEditModal(pattern, index)">{{ formatTarget(pattern, 2) }}</td>
      <td class="overview-cell" :class="{ 'is-empty': pattern.targeting3Type === 'なし' }" :title="formatTarget(pattern, 3)" @click="openEditModal(pattern, index)">{{ formatTarget(pattern, 3) }}</td>
      <td class="overview-cell" :class="{ 'is-empty': !pattern.notes }" :title="pattern.notes || ''" @click="openEditModal(pattern, index)">{{ pattern.notes || '—' }}</td>
    </tr>
  </tbody>
</table>
                  </div>
                  <div class="add-pattern-area">
                    <button type="button" class="add-new-btn" @click="addYoutubePattern">＋ New</button>
                  </div>
                </div>
              </div>
              <!-- Metaタブの場合 -->
              <div v-else-if="activePlatformTab === 'META' || activePlatformTab === 'Meta' || activePlatformTab === 'meta'" class="youtube-form-container">
                <div class="dark-card-wrapper">
                  <div class="table-scroll-container">
                    <table class="pattern-matrix-table" style="table-layout: fixed; min-width: 1788px; width: 100%;">
  <colgroup>
    <col style="width:48px;"><!-- 操作(削除/複製) -->
    <col style="width:30px;"><!-- # -->
    <col style="width:130px;"><!-- キャンペーン目的 -->
    <col style="width:90px;"><!-- KPI -->
    <col style="width:160px;"><!-- メニュー -->
    <col style="width:180px;"><!-- 配信面 -->
    <col style="width:90px;"><!-- 課金形態 -->
    <col style="width:110px;"><!-- 配信期間 -->
    <col style="width:100px;"><!-- 予算 -->
    <col style="width:100px;"><!-- 年齢 -->
    <col style="width:80px;"><!-- 性別 -->
    <col style="width:90px;"><!-- デバイス -->
    <col style="width:160px;"><!-- エリア -->
    <col style="width:110px;"><!-- FQ -->
    <col style="width:160px;"><!-- 興味関心 -->
    <col style="width:160px;"><!-- 備考 -->
  </colgroup>
  <thead>
    <tr>
      <th>操作</th><th>#</th><th>キャンペーン目的</th><th>KPI</th><th>メニュー</th><th>配信面</th>
      <th>課金形態</th><th>配信期間</th><th>予算</th><th>年齢</th><th>性別</th><th>デバイス</th><th>エリア</th>
      <th>FQ</th><th>興味関心</th><th>備考</th>
    </tr>
  </thead>
  <tbody>
    <tr v-for="(pattern, index) in metaPatterns" :key="pattern.id">
      <td class="row-action-cell">
        <div class="row-action-stack">
          <button type="button"
                  :disabled="metaPatterns.length <= 1"
                  @click="removeMetaPattern(index)"
                  title="削除"
                  class="row-action-btn row-action-delete">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>
          </button>
          <button type="button"
                  @click="copyMetaPattern(index)"
                  title="複製"
                  class="row-action-btn row-action-copy">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M6 15H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v1"/></svg>
          </button>
        </div>
      </td>
      <td class="text-center font-bold">{{ index + 1 }}</td>
      <td class="overview-cell" :class="{ 'is-empty': !pattern.campaignObjective }" :title="pattern.campaignObjective || '未設定'" @click="openMetaEditModal(pattern, index)">{{ pattern.campaignObjective || '未設定' }}</td>
      <td class="overview-cell" :class="{ 'is-empty': !pattern.kpi }" @click="openMetaEditModal(pattern, index)">{{ pattern.kpi || '未設定' }}</td>
      <td class="overview-cell" :class="{ 'is-empty': !pattern.menu }" :title="pattern.menu || '未設定'" @click="openMetaEditModal(pattern, index)">{{ pattern.menu || '未設定' }}</td>
      <td class="tag-cell" :class="{ 'is-empty': !pattern.placement.length }" :title="pattern.placement.join(' / ') || '未設定'" @click="openMetaEditModal(pattern, index)">
        <div class="tag-cell-inner">
          <template v-if="pattern.placement.length">
            <span v-for="v in pattern.placement" :key="v" class="notion-tag notion-tag-gray">{{ v }}</span>
          </template>
          <span v-else>未設定</span>
        </div>
      </td>
      <td class="overview-cell" @click="openMetaEditModal(pattern, index)">{{ pattern.billing }}</td>
      <td class="overview-cell" :title="formatMetaPeriod(pattern)" @click="openMetaEditModal(pattern, index)">{{ formatMetaPeriod(pattern) }}</td>
      <td class="overview-cell" :class="{ 'is-empty': !pattern.budget }" @click="openMetaEditModal(pattern, index)">{{ formatMetaBudget(pattern) }}</td>
      <td class="overview-cell" @click="openMetaEditModal(pattern, index)">{{ formatMetaAge(pattern) }}</td>
      <td class="overview-cell" @click="openMetaEditModal(pattern, index)">{{ pattern.gender }}</td>
      <td class="overview-cell" @click="openMetaEditModal(pattern, index)">{{ pattern.device }}</td>
      <td class="tag-cell" :class="{ 'is-empty': !pattern.prefNames.length }" :title="formatMetaArea(pattern)" @click="openMetaEditModal(pattern, index)">
        <div class="tag-cell-inner">
          <template v-if="pattern.prefNames.length">
            <span v-for="name in pattern.prefNames" :key="name" class="notion-tag notion-tag-gray">{{ name }}</span>
            <span v-if="pattern.city" class="tag-cell-extra">・{{ pattern.city }}</span>
          </template>
          <span v-else>未設定</span>
        </div>
      </td>
      <td class="overview-cell" @click="openMetaEditModal(pattern, index)">{{ formatMetaFq(pattern) }}</td>
      <td class="overview-cell" :class="{ 'is-empty': !pattern.interest }" :title="pattern.interest || ''" @click="openMetaEditModal(pattern, index)">{{ pattern.interest || '—' }}</td>
      <td class="overview-cell" :class="{ 'is-empty': !pattern.remarks }" :title="pattern.remarks || ''" @click="openMetaEditModal(pattern, index)">{{ pattern.remarks || '—' }}</td>
    </tr>
  </tbody>
</table>
                  </div>
                  <div class="add-pattern-area">
                    <button type="button" class="add-new-btn" @click="addMetaPattern">＋ New</button>
                  </div>
                </div>
              </div>
              <!-- YG-Display&DGCタブの場合 -->
              <div v-else-if="activePlatformTab === 'YG-Display&DGC' || activePlatformTab === 'yg'" class="youtube-form-container">
                <div class="dark-card-wrapper">
                  <div class="table-scroll-container">
                    <table class="pattern-matrix-table" style="table-layout: fixed; min-width: 1558px; width: 100%;">
  <colgroup>
    <col style="width:48px;"><!-- 操作(削除/複製) -->
    <col style="width:30px;"><!-- # -->
    <col style="width:170px;"><!-- メニュー -->
    <col style="width:70px;"><!-- 動画尺 -->
    <col style="width:90px;"><!-- 課金形態 -->
    <col style="width:80px;"><!-- 配信期間 -->
    <col style="width:100px;"><!-- 予算 -->
    <col style="width:150px;"><!-- エリア -->
    <col style="width:60px;"><!-- 性別 -->
    <col style="width:90px;"><!-- 年齢 -->
    <col style="width:140px;"><!-- ターゲティング① -->
    <col style="width:140px;"><!-- ターゲティング② -->
    <col style="width:140px;"><!-- ターゲティング③ -->
    <col style="width:90px;"><!-- デバイス -->
    <col style="width:160px;"><!-- 備考 -->
  </colgroup>
  <thead>
    <tr>
      <th>操作</th><th>#</th><th>メニュー</th><th>動画尺</th><th>課金形態</th>
      <th>配信期間</th><th>予算</th><th>エリア</th><th>性別</th><th>年齢</th>
      <th>ターゲティング①</th><th>ターゲティング②</th><th>ターゲティング③</th><th>デバイス</th><th>備考</th>
    </tr>
  </thead>
  <tbody>
    <tr v-for="(pattern, index) in ygPatterns" :key="pattern.id">
      <td class="row-action-cell">
        <div class="row-action-stack">
          <button type="button"
                  :disabled="ygPatterns.length <= 1"
                  @click="removeYgPattern(index)"
                  title="削除"
                  class="row-action-btn row-action-delete">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>
          </button>
          <button type="button"
                  @click="copyYgPattern(index)"
                  title="複製"
                  class="row-action-btn row-action-copy">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M6 15H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v1"/></svg>
          </button>
        </div>
      </td>
      <td class="text-center font-bold">{{ index + 1 }}</td>
      <td class="overview-cell" :class="{ 'is-empty': !pattern.menu }" :title="pattern.menu || '未設定'" @click="openYgEditModal(pattern, index)">{{ pattern.menu || '未設定' }}</td>
      <td class="overview-cell" :class="{ 'is-empty': !pattern.videoDuration }" @click="openYgEditModal(pattern, index)">{{ pattern.videoDuration ? pattern.videoDuration + '秒' : '未設定' }}</td>
      <td class="overview-cell" :class="{ 'is-empty': !pattern.billing }" @click="openYgEditModal(pattern, index)">{{ pattern.billing || '未設定' }}</td>
      <td class="overview-cell" :title="formatYgPeriod(pattern)" @click="openYgEditModal(pattern, index)">{{ formatYgPeriod(pattern) }}</td>
      <td class="overview-cell" :class="{ 'is-empty': !pattern.budget }" @click="openYgEditModal(pattern, index)">{{ formatYgBudget(pattern) }}</td>
      <td class="tag-cell" :class="{ 'is-empty': !pattern.prefNames.length }" :title="formatYgArea(pattern)" @click="openYgEditModal(pattern, index)">
        <div class="tag-cell-inner">
          <template v-if="pattern.prefNames.length">
            <span v-for="name in pattern.prefNames" :key="name" class="notion-tag notion-tag-gray">{{ name }}</span>
            <span v-if="pattern.city" class="tag-cell-extra">・{{ pattern.city }}</span>
          </template>
          <span v-else>未設定</span>
        </div>
      </td>
      <td class="overview-cell" @click="openYgEditModal(pattern, index)">{{ pattern.gender }}</td>
      <td class="overview-cell" :title="formatYgAge(pattern)" @click="openYgEditModal(pattern, index)">{{ formatYgAge(pattern) }}</td>
      <td class="overview-cell" :class="{ 'is-empty': pattern.targeting1Type === 'なし' }" :title="formatYgTargeting(pattern, 1)" @click="openYgEditModal(pattern, index)">{{ formatYgTargeting(pattern, 1) }}</td>
      <td class="overview-cell" :class="{ 'is-empty': pattern.targeting2Type === 'なし' }" :title="formatYgTargeting(pattern, 2)" @click="openYgEditModal(pattern, index)">{{ formatYgTargeting(pattern, 2) }}</td>
      <td class="overview-cell" :class="{ 'is-empty': pattern.targeting3Type === 'なし' }" :title="formatYgTargeting(pattern, 3)" @click="openYgEditModal(pattern, index)">{{ formatYgTargeting(pattern, 3) }}</td>
      <td class="tag-cell" :title="pattern.device.join(' / ')" @click="openYgEditModal(pattern, index)">
        <div class="tag-cell-inner">
          <span v-for="v in pattern.device" :key="v" class="notion-tag notion-tag-gray">{{ v }}</span>
        </div>
      </td>
      <td class="overview-cell" :class="{ 'is-empty': !pattern.remarks }" :title="pattern.remarks || ''" @click="openYgEditModal(pattern, index)">{{ pattern.remarks || '—' }}</td>
    </tr>
  </tbody>
</table>
                  </div>
                  <div class="add-pattern-area">
                    <button type="button" class="add-new-btn" @click="addYgPattern">＋ New</button>
                  </div>
                </div>
              </div>
              <!-- Listing -->
              <div v-else-if="activePlatformTab === 'LISTING' || activePlatformTab === 'Listing' || activePlatformTab === 'listing'" class="youtube-form-container" @click="activeFieldPopover = null">
                <!-- 依頼情報（依頼種別・LP・KW／案件全体で1回だけ入力） -->
                <div class="dark-card-wrapper" style="padding: 20px; margin-bottom: 16px;">
                  <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 20px;">
                    <div style="display: flex; flex-direction: column; gap: 20px;">
                      <div>
                        <label class="unified-font listing-info-label"><span class="req">*</span>依頼種別</label>
                        <div class="notion-select-wrap">
                          <button type="button" class="notion-select-trigger notion-select-trigger-multi" @click.stop="toggleFieldPopover('listing-requestType')">
                            <template v-if="listingInfo.requestTypes.length">
                              <span v-for="v in listingInfo.requestTypes" :key="v" class="notion-tag notion-tag-gray">{{ v }}</span>
                            </template>
                            <span v-else class="notion-select-placeholder">1つ以上選択してください</span>
                          </button>
                          <div v-if="activeFieldPopover === 'listing-requestType'" class="notion-select-panel" @click.stop>
                            <button
                              v-for="opt in listingRequestTypeOptions"
                              :key="opt"
                              type="button"
                              class="notion-option-row"
                              @click="toggleListingRequestType(opt)"
                            >
                              <span class="notion-tag notion-tag-gray">{{ opt }}</span>
                              <svg v-if="listingInfo.requestTypes.includes(opt)" class="notion-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg>
                            </button>
                          </div>
                        </div>
                      </div>
                      <div>
                        <label class="unified-font listing-info-label">LP</label>
                        <input v-model="listingInfo.lp" type="text" class="matrix-input transparent-input" placeholder="例：https://example.com/lp" style="width: 100%; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
                      </div>
                    </div>
                    <div>
                      <label class="unified-font listing-info-label">部分一致（インテントマッチ）KW</label>
                      <textarea v-model="listingInfo.kwBroad" class="unified-font custom-textarea transparent-input" placeholder="想定KWがある場合は入力（※基本こちらを推奨）" style="width: 100%; min-height: 160px; border: 1px solid #ddd; border-radius: 4px; padding: 8px 10px;"></textarea>
                    </div>
                    <div>
                      <label class="unified-font listing-info-label">フレーズ一致KW（任意）</label>
                      <textarea v-model="listingInfo.kwPhrase" class="unified-font custom-textarea transparent-input" style="width: 100%; min-height: 160px; border: 1px solid #ddd; border-radius: 4px; padding: 8px 10px;"></textarea>
                    </div>
                    <div>
                      <label class="unified-font listing-info-label">完全一致KW（任意）</label>
                      <textarea v-model="listingInfo.kwExact" class="unified-font custom-textarea transparent-input" style="width: 100%; min-height: 160px; border: 1px solid #ddd; border-radius: 4px; padding: 8px 10px;"></textarea>
                    </div>
                  </div>
                </div>
                <div class="dark-card-wrapper">
                  <div class="table-scroll-container">
                    <table class="pattern-matrix-table" style="table-layout: fixed; min-width: 1180px; width: 100%;">
  <colgroup>
    <col style="width:48px;"><!-- 操作(削除/複製) -->
    <col style="width:30px;"><!-- # -->
    <col style="width:90px;"><!-- メニュー -->
    <col style="width:100px;"><!-- 配信期間 -->
    <col style="width:100px;"><!-- 予算 -->
    <col style="width:150px;"><!-- エリア -->
    <col style="width:70px;"><!-- 性別 -->
    <col style="width:110px;"><!-- 年齢 -->
    <col style="width:110px;"><!-- デバイス -->
    <col style="width:160px;"><!-- 備考 -->
  </colgroup>
  <thead>
    <tr>
      <th>操作</th><th>#</th><th>メニュー</th><th>配信期間</th><th>予算</th>
      <th>エリア</th><th>性別</th><th>年齢</th><th>デバイス</th><th>備考</th>
    </tr>
  </thead>
  <tbody>
    <tr v-for="(pattern, index) in listingPatterns" :key="pattern.id">
      <td class="row-action-cell">
        <div class="row-action-stack">
          <button type="button"
                  :disabled="listingPatterns.length <= 1"
                  @click="removeListingPattern(index)"
                  title="削除"
                  class="row-action-btn row-action-delete">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>
          </button>
          <button type="button"
                  @click="copyListingPattern(index)"
                  title="複製"
                  class="row-action-btn row-action-copy">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M6 15H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v1"/></svg>
          </button>
        </div>
      </td>
      <td class="text-center font-bold">{{ index + 1 }}</td>
      <td class="overview-cell" :class="{ 'is-empty': !pattern.menu }" @click="openListingEditModal(pattern, index)">{{ pattern.menu || '未設定' }}</td>
      <td class="overview-cell" @click="openListingEditModal(pattern, index)">{{ formatListingPeriod(pattern) }}</td>
      <td class="overview-cell" :class="{ 'is-empty': !pattern.budget }" @click="openListingEditModal(pattern, index)">{{ formatListingBudget(pattern) }}</td>
      <td class="tag-cell" :title="formatListingArea(pattern)" @click="openListingEditModal(pattern, index)">
        <div class="tag-cell-inner">
          <template v-if="pattern.prefNames.length">
            <span v-for="name in pattern.prefNames" :key="name" class="notion-tag notion-tag-gray">{{ name }}</span>
            <span v-if="pattern.city" class="tag-cell-extra">・{{ pattern.city }}</span>
          </template>
          <span v-else>未設定</span>
        </div>
      </td>
      <td class="overview-cell" @click="openListingEditModal(pattern, index)">{{ pattern.gender }}</td>
      <td class="overview-cell" :title="formatListingAge(pattern)" @click="openListingEditModal(pattern, index)">{{ formatListingAge(pattern) }}</td>
      <td class="tag-cell" :title="pattern.device.join(' / ')" @click="openListingEditModal(pattern, index)">
        <div class="tag-cell-inner">
          <span v-for="v in pattern.device" :key="v" class="notion-tag notion-tag-gray">{{ v }}</span>
        </div>
      </td>
      <td class="overview-cell" :class="{ 'is-empty': !pattern.remarks }" :title="pattern.remarks || ''" @click="openListingEditModal(pattern, index)">{{ pattern.remarks || '—' }}</td>
    </tr>
  </tbody>
</table>
                  </div>
                  <div class="add-pattern-area">
                    <button type="button" class="add-new-btn" @click="addListingPattern">＋ New</button>
                  </div>
                </div>
              </div>
              <!-- その他のタブの場合 -->
              <div v-else class="dark-card-wrapper" style="padding: 20px; color: #fff;">
                <p>{{ activePlatformTab }}用のテーブルは準備中です</p>
              </div>
              </div>
              </transition>
            </div>
            <!-- フッター戻る・送信ボタン -->
            <div class="action-area step2-actions" style="margin-top: 30px;">
              <button type="button" class="back-btn" @click="currentStep = 2">&larr; PLATFORMSに戻る</button>
              <button type="button" class="next-btn animated-btn visible" :disabled="isSubmitting" @click="submitForm">
                <span>{{ isSubmitting ? '送信中...' : '送信する' }}</span>
                <svg class="btn-submit-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4 20-7Z"/></svg>
              </button>
            </div>
          </section>
        </div>
      </transition>
      <!-- 編集用モーダル（ENTRY風の縦並びUIでYouTubeの各項目を編集・テーブルに登録） -->
      <div v-if="isEditModalOpen" class="modal-overlay" @click.self="closeEditModal">
        <div class="modal-content youtube-modal-content" style="max-width: 700px; width: 90%; max-height: 90vh; overflow-y: auto;" @click="activeFieldPopover = null">
          <div class="modal-header">
            <h3>YouTube パターン詳細編集</h3>
            <button type="button" class="btn-close" @click="closeEditModal">×</button>
          </div>
          
          <div class="form-body-vertical" style="padding: 20px;">
            <!-- ① メニュー -->
            <div class="form-group">
              <label class="unified-font"><span class="num">1</span> <span class="req">*</span>メニュー</label>
              <div class="notion-select-wrap">
                <button type="button" class="notion-select-trigger" @click.stop="toggleFieldPopover('menu')">
                  <span v-if="editingPattern.menu" class="notion-tag" :class="'notion-tag-' + getMenuColor(editingPattern.menu)">{{ editingPattern.menu }}</span>
                  <span v-else class="notion-select-placeholder">選択してください</span>
                </button>
                <div v-if="activeFieldPopover === 'menu'" class="notion-select-panel" @click.stop>
                  <button
                    v-for="opt in menuOptions"
                    :key="opt.value"
                    type="button"
                    class="notion-option-row"
                    @click="selectMenu(opt.value)"
                  >
                    <span class="notion-tag" :class="'notion-tag-' + opt.color">{{ opt.value }}</span>
                    <svg v-if="editingPattern.menu === opt.value" class="notion-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg>
                  </button>
                </div>
              </div>
            </div>
            <!-- ② 配信面（複数選択） -->
            <div class="form-group">
              <label class="unified-font"><span class="num">2</span> 配信面</label>
              <div class="notion-select-wrap">
                <button
                  type="button"
                  class="notion-select-trigger notion-select-trigger-multi"
                  :disabled="isYoutubePlacementLocked"
                  @click.stop="!isYoutubePlacementLocked && toggleFieldPopover('placement')"
                >
                  <template v-if="editingPattern.placement && editingPattern.placement.length">
                    <span v-for="v in editingPattern.placement" :key="v" class="notion-tag notion-tag-gray">{{ v }}</span>
                  </template>
                  <span v-else class="notion-select-placeholder">選択してください</span>
                </button>
                <div v-if="activeFieldPopover === 'placement' && !isYoutubePlacementLocked" class="notion-select-panel" @click.stop>
                  <button
                    v-for="v in getYoutubePlacements(editingPattern.menu)"
                    :key="v"
                    type="button"
                    class="notion-option-row"
                    @click="togglePlacementOption(v)"
                  >
                    <span class="notion-tag notion-tag-gray">{{ v }}</span>
                    <svg v-if="editingPattern.placement.includes(v)" class="notion-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg>
                  </button>
                  <p v-if="!getYoutubePlacements(editingPattern.menu).length" class="notion-panel-empty">先にメニューを選択してください</p>
                </div>
                <p v-if="isYoutubePlacementLocked" style="margin: 6px 0 0; font-size: 11px; color: #9a9a9a;">※スキップ不可はインストリーム固定です</p>
              </div>
            </div>
            <!-- ③ 広告素材（縦型 / 横型） -->
            <div class="form-group">
              <label class="unified-font"><span class="num">3</span> 広告素材</label>
              <div style="display: flex; gap: 10px; margin-top: 5px;">
                <select v-model="editingPattern.verticalCreative" @change="onYoutubeCreativeChange" class="matrix-select transparent-input" :class="{ 'is-placeholder': !editingPattern.verticalCreative }" style="flex: 1; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
                  <option value="">縦型を選択</option>
                  <option
                    v-for="v in verticalCreativeOptions"
                    :key="v"
                    :value="v"
                    :disabled="v === 'なし' && editingPattern.horizontalCreative === 'なし'"
                  >{{ v }}</option>
                </select>
                <select v-model="editingPattern.horizontalCreative" @change="onYoutubeCreativeChange" class="matrix-select transparent-input" :class="{ 'is-placeholder': !editingPattern.horizontalCreative }" style="flex: 1; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
                  <option value="">横型を選択</option>
                  <option
                    v-for="v in horizontalCreativeOptions"
                    :key="v"
                    :value="v"
                    :disabled="v === 'なし' && editingPattern.verticalCreative === 'なし'"
                  >{{ v }}</option>
                </select>
              </div>
            </div>
            <!-- ④ デバイス（複数選択・ALL排他） -->
            <div class="form-group">
              <label class="unified-font"><span class="num">4</span> デバイス</label>
              <div class="notion-select-wrap">
                <button type="button" class="notion-select-trigger notion-select-trigger-multi" :disabled="isYoutubeDeviceLocked" @click.stop="toggleFieldPopover('device')">
                  <span v-for="v in editingPattern.device" :key="v" class="notion-tag notion-tag-gray">{{ deviceLabelMap[v] || v }}</span>
                </button>
                <p v-if="isYoutubeDeviceLocked" style="margin: 6px 0 0; font-size: 11px; color: #9a9a9a;">※スキップ不可×30秒はCTV限定です</p>
                <div v-if="activeFieldPopover === 'device'" class="notion-select-panel" @click.stop>
                  <button
                    v-for="opt in deviceOptions"
                    :key="opt.value"
                    type="button"
                    class="notion-option-row"
                    @click="toggleDeviceOption(opt.value)"
                  >
                    <span class="notion-tag notion-tag-gray">{{ opt.label }}</span>
                    <svg v-if="editingPattern.device.includes(opt.value)" class="notion-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg>
                  </button>
                </div>
              </div>
            </div>
            <!-- ⑤ 配信期間 -->
            <div class="form-group">
              <label class="unified-font"><span class="num">5</span> 配信期間</label>
              <div class="period-wrap" style="display: flex; gap: 8px; align-items: center; margin-top: 5px;">
                <input v-model.number="editingPattern.periodNumber" type="number" min="1" class="matrix-input transparent-input" placeholder="数値" style="width: 64px; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 8px; text-align: center;">
                <select v-model="editingPattern.periodUnit" class="matrix-select transparent-input" style="width: 100px; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
                  <option value="日間">日間</option>
                  <option value="ヶ月">ヶ月</option>
                </select>
              </div>
            </div>
            <!-- ⑥ 予算 -->
            <div class="form-group">
              <label class="unified-font"><span class="num">6</span> 予算</label>
              <input v-model="editingBudgetDisplay" type="text" inputmode="numeric" class="matrix-input transparent-input" placeholder="例: ¥1,000,000" style="width: 100%; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
            </div>
            <!-- ⑦ エリア -->
            <div class="form-group">
              <label class="unified-font"><span class="num">7</span> エリア</label>
              <div style="display: flex; gap: 10px; margin-top: 5px; align-items: flex-start;">
                <button
                  type="button"
                  class="notion-select-trigger notion-select-trigger-multi"
                  style="flex: 1.4;"
                  @click="openPrefModal('editing')"
                >
                  <template v-if="editingPattern.prefNames && editingPattern.prefNames.length">
                    <span v-for="name in editingPattern.prefNames" :key="name" class="notion-tag notion-tag-gray">{{ name }}</span>
                  </template>
                  <span v-else class="notion-select-placeholder">都道府県を選択</span>
                </button>
                <input
                  v-model="editingPattern.city"
                  type="text"
                  class="matrix-input transparent-input"
                  placeholder="市区町村"
                  :disabled="editingPattern.prefNames && editingPattern.prefNames.includes('全国')"
                  style="flex: 1; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;"
                >
              </div>
            </div>
            <!-- ⑧ 性別（複数選択・ALL排他） -->
            <div class="form-group">
              <label class="unified-font"><span class="num">8</span> 性別</label>
              <div class="notion-select-wrap">
                <button type="button" class="notion-select-trigger notion-select-trigger-multi" @click.stop="toggleFieldPopover('gender')">
                  <span v-for="v in editingPattern.gender" :key="v" class="notion-tag notion-tag-gray">{{ genderLabelMap[v] || v }}</span>
                </button>
                <div v-if="activeFieldPopover === 'gender'" class="notion-select-panel" @click.stop>
                  <button
                    v-for="opt in genderOptions"
                    :key="opt.value"
                    type="button"
                    class="notion-option-row"
                    @click="toggleGenderOption(opt.value)"
                  >
                    <span class="notion-tag notion-tag-gray">{{ opt.label }}</span>
                    <svg v-if="editingPattern.gender.includes(opt.value)" class="notion-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg>
                  </button>
                </div>
              </div>
            </div>
            <!-- ⑨ 年齢 -->
            <div class="form-group">
              <label class="unified-font"><span class="num">9</span> 年齢</label>
              <div style="display: flex; gap: 10px; margin-top: 5px;">
                <select v-model="editingPattern.age1" @change="onAge1Change" class="matrix-select transparent-input" style="flex: 1; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
                  <option v-for="v in age1Options" :key="v" :value="v">{{ v }}</option>
                </select>
                <select v-model="editingPattern.age2" :disabled="isAge2Disabled" class="matrix-select transparent-input" :class="{ 'is-placeholder': !editingPattern.age2 }" style="flex: 1; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
                  <option v-for="v in age2Options(editingPattern.age1)" :key="v" :value="v">{{ v }}</option>
                </select>
                <select v-model="editingPattern.age3" :disabled="isAge3Disabled" class="matrix-select transparent-input" style="flex: 1; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
                  <option v-for="v in age3Options" :key="v" :value="v">{{ v }}</option>
                </select>
              </div>
            </div>
            <!-- ⑩⑪⑫ ターゲティング①②③ -->
            <div class="form-group" v-for="n in [1, 2, 3]" :key="'youtube-targeting-' + n">
              <label class="unified-font"><span class="num">{{ 9 + n }}</span> ターゲティング{{ ['①', '②', '③'][n - 1] }}</label>
              <div style="display: flex; gap: 10px; margin-top: 5px;">
                <select
                  v-model="editingPattern[`targeting${n}Type`]"
                  @change="onTargetingTypeChange(n)"
                  class="matrix-select transparent-input"
                  style="flex: 1; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;"
                >
                  <option v-for="t in targetingTypeOptions" :key="t" :value="t">{{ t }}</option>
                </select>
                <button
                  type="button"
                  class="notion-select-trigger notion-select-trigger-multi"
                  style="flex: 1.4;"
                  :disabled="!editingPattern[`targeting${n}Type`] || editingPattern[`targeting${n}Type`] === 'なし'"
                  @click="openTargetModal(n)"
                >
                  <template v-if="editingPattern[`targeting${n}Values`] && editingPattern[`targeting${n}Values`].length">
                    <span v-for="v in editingPattern[`targeting${n}Values`]" :key="v" class="notion-tag notion-tag-gray">{{ v }}</span>
                  </template>
                  <span v-else class="notion-select-placeholder">
                    {{ !editingPattern[`targeting${n}Type`] || editingPattern[`targeting${n}Type`] === 'なし' ? '－' : (targetingFreeTextTypes.includes(editingPattern[`targeting${n}Type`]) ? '詳細を入力' : 'カテゴリを選択') }}
                  </span>
                </button>
              </div>
            </div>
            <!-- ⑬ 備考 -->
            <div class="form-group">
              <label class="unified-font"><span class="num">13</span> 備考</label>
              <textarea class="unified-font custom-textarea transparent-input" v-model="editingPattern.notes" placeholder="補足事項があれば入力してください" style="width: 100%; border: 1px solid #ddd; border-radius: 4px; padding: 10px;"></textarea>
            </div>
          </div>
          <div class="modal-footer" style="padding: 15px 20px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #eee;">
            <button type="button" class="back-btn" @click="closeEditModal">キャンセル</button>
            <button type="button" class="back-btn modal-confirm-btn" @click="saveEditModal">保存する</button>
          </div>
        </div>
      </div>
      <!-- Meta パターン詳細編集モーダル -->
      <div v-if="isMetaEditModalOpen" class="modal-overlay" @click.self="closeMetaEditModal">
        <div class="modal-content youtube-modal-content" style="max-width: 700px; width: 90%; max-height: 90vh; overflow-y: auto;" @click="activeFieldPopover = null">
          <div class="modal-header">
            <h3>META パターン詳細編集</h3>
            <button type="button" class="btn-close" @click="closeMetaEditModal">×</button>
          </div>
          <div class="form-body-vertical" style="padding: 20px;">
            <!-- ① キャンペーン目的 -->
            <div class="form-group">
              <label class="unified-font"><span class="num">1</span> <span class="req">*</span>キャンペーン目的</label>
              <select
                v-model="editingMetaPattern.campaignObjective"
                @change="onMetaObjectiveChange"
                class="matrix-select transparent-input"
                :class="{ 'is-placeholder': !editingMetaPattern.campaignObjective }"
                style="width: 100%; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;"
              >
                <option value="">選択してください</option>
                <option v-for="o in metaCampaignObjectives" :key="o" :value="o">{{ o }}</option>
              </select>
            </div>
            <!-- ② KPI・メニュー -->
            <div class="form-group">
              <label class="unified-font"><span class="num">2</span> KPI / メニュー</label>
              <div style="display: flex; gap: 10px; margin-top: 5px;">
                <select
                  v-model="editingMetaPattern.kpi"
                  :disabled="isMetaKpiFixed(editingMetaPattern.campaignObjective) || !editingMetaPattern.campaignObjective"
                  class="matrix-select transparent-input"
                  :class="{ 'is-placeholder': !editingMetaPattern.kpi }"
                  style="flex: 1; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;"
                >
                  <option value="">KPI選択</option>
                  <option v-for="k in getMetaKpiChoices(editingMetaPattern.campaignObjective)" :key="k" :value="k">{{ k }}</option>
                </select>
                <select
                  v-model="editingMetaPattern.menu"
                  @change="onMetaMenuChange"
                  :disabled="getMetaMenuOptions(editingMetaPattern.campaignObjective).length <= 1"
                  class="matrix-select transparent-input"
                  :class="{ 'is-placeholder': !editingMetaPattern.menu }"
                  style="flex: 1.4; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;"
                >
                  <option value="">メニュー選択</option>
                  <option v-for="m in getMetaMenuOptions(editingMetaPattern.campaignObjective)" :key="m" :value="m">{{ m }}</option>
                </select>
              </div>
            </div>
            <!-- ③ 配信面（複数選択） -->
            <div class="form-group">
              <label class="unified-font"><span class="num">3</span> 配信面</label>
              <div class="notion-select-wrap">
                <button
                  type="button"
                  class="notion-select-trigger notion-select-trigger-multi"
                  :disabled="!editingMetaPattern.menu"
                  @click.stop="toggleFieldPopover('meta-placement')"
                >
                  <template v-if="editingMetaPattern.placement && editingMetaPattern.placement.length">
                    <span v-for="v in editingMetaPattern.placement" :key="v" class="notion-tag notion-tag-gray">{{ v }}</span>
                  </template>
                  <span v-else class="notion-select-placeholder">{{ editingMetaPattern.menu ? '選択してください' : 'メニューを先に選択' }}</span>
                </button>
                <div v-if="activeFieldPopover === 'meta-placement'" class="notion-select-panel" @click.stop>
                  <button
                    v-for="v in metaPlacementOptions"
                    :key="v"
                    type="button"
                    class="notion-option-row"
                    @click="toggleMetaPlacement(v)"
                  >
                    <span class="notion-tag notion-tag-gray">{{ v }}</span>
                    <svg v-if="editingMetaPattern.placement.includes(v)" class="notion-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg>
                  </button>
                </div>
              </div>
            </div>
            <!-- ④ 課金形態 -->
            <div class="form-group">
              <label class="unified-font"><span class="num">4</span> 課金形態</label>
              <input :value="editingMetaPattern.billing" disabled class="matrix-input transparent-input" style="width: 100%; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
            </div>
            <!-- ⑤ 配信期間 -->
            <div class="form-group">
              <label class="unified-font"><span class="num">5</span> 配信期間</label>
              <div style="display: flex; gap: 8px; align-items: center; margin-top: 5px;">
                <input v-model.number="editingMetaPattern.periodNumber" type="number" min="1" class="matrix-input transparent-input" placeholder="数値" style="width: 64px; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 8px; text-align: center;">
                <select v-model="editingMetaPattern.periodUnit" class="matrix-select transparent-input" style="width: 100px; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
                  <option value="ヶ月間">ヶ月間</option>
                  <option value="日間">日間</option>
                </select>
              </div>
              <input v-model="editingMetaPattern.periodTiming" type="text" class="matrix-input transparent-input" placeholder="出稿予定時期（任意）" style="width: 100%; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px; margin-top: 8px;">
            </div>
            <!-- ⑥ 予算 -->
            <div class="form-group">
              <label class="unified-font"><span class="num">6</span> 予算</label>
              <input v-model="editingMetaBudgetDisplay" type="text" inputmode="numeric" class="matrix-input transparent-input" placeholder="例: ¥500,000" style="width: 100%; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
            </div>
            <!-- ⑦ 年齢 -->
            <div class="form-group">
              <label class="unified-font"><span class="num">7</span> 年齢（①13〜65歳・1歳単位／②任意）</label>
              <div style="display: flex; gap: 10px; margin-top: 5px;">
                <input v-model.number="editingMetaPattern.age1" type="number" min="13" max="65" class="matrix-input transparent-input" style="flex: 1; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
                <input v-model.number="editingMetaPattern.age2" type="number" :min="editingMetaPattern.age1 + 1" max="65" :disabled="isMetaAge2Disabled" placeholder="任意" class="matrix-input transparent-input" style="flex: 1; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
              </div>
              <p style="margin: 6px 0 0; font-size: 11px; color: #9a9a9a;">※17歳以下は性別・市区町村・興味関心が入力不可になります</p>
            </div>
            <!-- ⑧ 性別 -->
            <div class="form-group">
              <label class="unified-font"><span class="num">8</span> 性別</label>
              <select v-model="editingMetaPattern.gender" :disabled="isMetaUnder18" class="matrix-select transparent-input" style="width: 100%; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
                <option value="男女">男女</option>
                <option value="男性のみ">男性のみ</option>
                <option value="女性のみ">女性のみ</option>
              </select>
            </div>
            <!-- ⑨ デバイス -->
            <div class="form-group">
              <label class="unified-font"><span class="num">9</span> デバイス</label>
              <select v-model="editingMetaPattern.device" class="matrix-select transparent-input" style="width: 100%; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
                <option value="ALL（SD＋PC）">ALL（SD＋PC）</option>
                <option value="SD">SD</option>
                <option value="PC">PC</option>
              </select>
            </div>
            <!-- ⑩ エリア -->
            <div class="form-group">
              <label class="unified-font"><span class="num">10</span> エリア</label>
              <div style="display: flex; gap: 10px; margin-top: 5px; align-items: flex-start;">
                <button
                  type="button"
                  class="notion-select-trigger notion-select-trigger-multi"
                  style="flex: 1.4;"
                  @click="openPrefModal('editing-meta')"
                >
                  <template v-if="editingMetaPattern.prefNames && editingMetaPattern.prefNames.length">
                    <span v-for="name in editingMetaPattern.prefNames" :key="name" class="notion-tag notion-tag-gray">{{ name }}</span>
                  </template>
                  <span v-else class="notion-select-placeholder">都道府県を選択</span>
                </button>
                <input
                  v-model="editingMetaPattern.city"
                  type="text"
                  class="matrix-input transparent-input"
                  placeholder="市区町村"
                  :disabled="isMetaCityDisabled"
                  style="flex: 1; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;"
                >
              </div>
            </div>
            <!-- ⑪ FQ -->
            <div class="form-group">
              <label class="unified-font"><span class="num">11</span> FQ</label>
              <div style="display: flex; gap: 16px; margin-top: 5px; align-items: center;">
                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.9rem; cursor: pointer;">
                  <input type="radio" value="推奨" v-model="editingMetaPattern.fqType" @change="onMetaFqTypeChange">
                  推奨
                </label>
                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.9rem; cursor: pointer;">
                  <input type="radio" value="指定" v-model="editingMetaPattern.fqType" @change="onMetaFqTypeChange">
                  指定
                </label>
              </div>
              <div v-if="editingMetaPattern.fqType === '指定'" style="display: flex; gap: 8px; align-items: center; margin-top: 8px;">
                <input v-model.number="editingMetaPattern.fqNum" type="number" min="1" placeholder="回数" class="matrix-input transparent-input" style="width: 80px; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 8px; text-align: center;">
                <span class="unified-font">回 /</span>
                <input v-model.number="editingMetaPattern.fqDays" type="number" min="1" placeholder="日数" class="matrix-input transparent-input" style="width: 80px; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 8px; text-align: center;">
                <span class="unified-font">日間</span>
              </div>
            </div>
            <!-- ⑫ 興味関心 -->
            <div class="form-group">
              <label class="unified-font"><span class="num">12</span> 興味関心</label>
              <textarea v-model="editingMetaPattern.interest" :disabled="isMetaUnder18" class="unified-font custom-textarea transparent-input" placeholder="興味関心を記入" style="width: 100%; border: 1px solid #ddd; border-radius: 4px; padding: 10px;"></textarea>
            </div>
            <!-- ⑬ 備考 -->
            <div class="form-group">
              <label class="unified-font"><span class="num">13</span> 備考</label>
              <textarea v-model="editingMetaPattern.remarks" class="unified-font custom-textarea transparent-input" placeholder="補足事項があれば入力してください" style="width: 100%; border: 1px solid #ddd; border-radius: 4px; padding: 10px;"></textarea>
            </div>
          </div>
          <div class="modal-footer" style="padding: 15px 20px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #eee;">
            <button type="button" class="back-btn" @click="closeMetaEditModal">キャンセル</button>
            <button type="button" class="back-btn modal-confirm-btn" @click="saveMetaEditModal">保存する</button>
          </div>
        </div>
      </div>
      <!-- YG パターン詳細編集モーダル -->
      <div v-if="isYgEditModalOpen" class="modal-overlay" @click.self="closeYgEditModal">
        <div class="modal-content youtube-modal-content" style="max-width: 700px; width: 90%; max-height: 90vh; overflow-y: auto;" @click="activeFieldPopover = null">
          <div class="modal-header">
            <h3>YG-Display&DGC パターン詳細編集</h3>
            <button type="button" class="btn-close" @click="closeYgEditModal">×</button>
          </div>
          <div class="form-body-vertical" style="padding: 20px;">
            <!-- ① メニュー -->
            <div class="form-group">
              <label class="unified-font"><span class="num">1</span> <span class="req">*</span>メニュー</label>
              <select
                v-model="editingYgPattern.menu"
                @change="onYgMenuChange"
                class="matrix-select transparent-input"
                :class="{ 'is-placeholder': !editingYgPattern.menu }"
                style="width: 100%; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;"
              >
                <option value="">選択してください</option>
                <option v-for="m in ygMenuOptions" :key="m" :value="m">{{ m }}</option>
              </select>
            </div>
            <!-- ② 動画尺・課金形態 -->
            <div class="form-group">
              <label class="unified-font"><span class="num">2</span> 動画尺 / 課金形態</label>
              <div style="display: flex; gap: 10px; margin-top: 5px;">
                <div style="flex: 1; display: flex; align-items: center; gap: 6px;">
                  <input
                    v-model.number="editingYgPattern.videoDuration"
                    type="number"
                    min="5"
                    :max="ygIsDgcVideo(editingYgPattern.menu) ? null : 60"
                    :disabled="!ygIsVideoMenu(editingYgPattern.menu)"
                    placeholder="例: 15"
                    class="matrix-input transparent-input"
                    style="width: 80px; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 8px; text-align: center;"
                  >
                  <span class="unified-font">秒</span>
                </div>
                <select
                  v-model="editingYgPattern.billing"
                  :disabled="ygIsBillingFixed(editingYgPattern.menu) || !editingYgPattern.menu"
                  class="matrix-select transparent-input"
                  :class="{ 'is-placeholder': !editingYgPattern.billing }"
                  style="flex: 1.4; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;"
                >
                  <option value="">{{ editingYgPattern.menu ? '課金形態を選択' : 'メニューを先に選択' }}</option>
                  <option v-for="b in ygBillingOptions(editingYgPattern.menu)" :key="b" :value="b">{{ b }}</option>
                </select>
              </div>
            </div>
            <!-- ③ 配信期間 -->
            <div class="form-group">
              <label class="unified-font"><span class="num">3</span> 配信期間</label>
              <div style="display: flex; gap: 8px; align-items: center; margin-top: 5px;">
                <input v-model.number="editingYgPattern.periodNumber" type="number" min="1" class="matrix-input transparent-input" placeholder="数値" style="width: 64px; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 8px; text-align: center;">
                <select v-model="editingYgPattern.periodUnit" class="matrix-select transparent-input" style="width: 100px; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
                  <option value="ヶ月間">ヶ月間</option>
                  <option value="日間">日間</option>
                </select>
              </div>
            </div>
            <!-- ④ 予算 -->
            <div class="form-group">
              <label class="unified-font"><span class="num">4</span> 予算</label>
              <input v-model="editingYgBudgetDisplay" type="text" inputmode="numeric" class="matrix-input transparent-input" placeholder="例: ¥500,000" style="width: 100%; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
            </div>
            <!-- ⑤ エリア -->
            <div class="form-group">
              <label class="unified-font"><span class="num">5</span> エリア</label>
              <div style="display: flex; gap: 10px; margin-top: 5px; align-items: flex-start;">
                <button
                  type="button"
                  class="notion-select-trigger notion-select-trigger-multi"
                  style="flex: 1.4;"
                  @click="openPrefModal('editing-yg')"
                >
                  <template v-if="editingYgPattern.prefNames && editingYgPattern.prefNames.length">
                    <span v-for="name in editingYgPattern.prefNames" :key="name" class="notion-tag notion-tag-gray">{{ name }}</span>
                  </template>
                  <span v-else class="notion-select-placeholder">都道府県を選択</span>
                </button>
                <input
                  v-model="editingYgPattern.city"
                  type="text"
                  class="matrix-input transparent-input"
                  placeholder="市区町村"
                  :disabled="editingYgPattern.prefNames && editingYgPattern.prefNames.includes('全国')"
                  style="flex: 1; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;"
                >
              </div>
            </div>
            <!-- ⑥ 性別 -->
            <div class="form-group">
              <label class="unified-font"><span class="num">6</span> 性別</label>
              <select v-model="editingYgPattern.gender" class="matrix-select transparent-input" style="width: 100%; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
                <option value="ALL">ALL</option>
                <option value="男性">男性</option>
                <option value="女性">女性</option>
              </select>
            </div>
            <!-- ⑦ 年齢 -->
            <div class="form-group">
              <label class="unified-font"><span class="num">7</span> 年齢</label>
              <div style="display: flex; gap: 10px; margin-top: 5px;">
                <select v-model="editingYgPattern.age1" @change="onYgAge1Change" class="matrix-select transparent-input" :class="{ 'is-placeholder': !editingYgPattern.age1 }" style="flex: 1; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
                  <option v-for="v in ygAge1Options(editingYgPattern.menu)" :key="v" :value="v">{{ v }}</option>
                </select>
                <select v-model="editingYgPattern.age2" :disabled="isYgAge2Disabled" class="matrix-select transparent-input" :class="{ 'is-placeholder': !editingYgPattern.age2 }" style="flex: 1; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
                  <option v-for="v in ygAge2Options(editingYgPattern.menu, editingYgPattern.age1)" :key="v" :value="v">{{ v }}</option>
                </select>
                <select v-model="editingYgPattern.age3" :disabled="isYgAge3Disabled" class="matrix-select transparent-input" :class="{ 'is-placeholder': !editingYgPattern.age3 }" style="flex: 1; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
                  <option value="">-</option>
                  <option value="不明あり">不明あり</option>
                  <option value="不明なし">不明なし</option>
                </select>
              </div>
            </div>
            <!-- ⑧⑨⑩ ターゲティング①②③ -->
            <div class="form-group" v-for="n in [1, 2, 3]" :key="'yg-targeting-' + n">
              <label class="unified-font"><span class="num">{{ 7 + n }}</span> ターゲティング{{ ['①', '②', '③'][n - 1] }}</label>
              <div style="display: flex; gap: 10px; margin-top: 5px;">
                <select
                  v-model="editingYgPattern[`targeting${n}Type`]"
                  @change="onYgTargetingTypeChange(n)"
                  :disabled="!editingYgPattern.menu"
                  class="matrix-select transparent-input"
                  style="flex: 1; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;"
                >
                  <option v-for="t in ygTargetingTypeOptions(editingYgPattern.menu)" :key="t" :value="t">{{ t }}</option>
                </select>
                <button
                  type="button"
                  class="notion-select-trigger notion-select-trigger-multi"
                  style="flex: 1.4;"
                  :disabled="!editingYgPattern[`targeting${n}Type`] || editingYgPattern[`targeting${n}Type`] === 'なし'"
                  @click="openYgTargetModal(n)"
                >
                  <template v-if="editingYgPattern[`targeting${n}Values`] && editingYgPattern[`targeting${n}Values`].length">
                    <span v-for="v in editingYgPattern[`targeting${n}Values`]" :key="v" class="notion-tag notion-tag-gray">{{ v }}</span>
                  </template>
                  <span v-else class="notion-select-placeholder">
                    {{ !editingYgPattern[`targeting${n}Type`] || editingYgPattern[`targeting${n}Type`] === 'なし' ? '－' : (ygTargetingFreeTextTypes.includes(editingYgPattern[`targeting${n}Type`]) ? '詳細を入力' : 'カテゴリを選択') }}
                  </span>
                </button>
              </div>
            </div>
            <!-- ⑪ デバイス（複数選択） -->
            <div class="form-group">
              <label class="unified-font"><span class="num">11</span> デバイス</label>
              <div class="notion-select-wrap">
                <button
                  type="button"
                  class="notion-select-trigger notion-select-trigger-multi"
                  :disabled="ygIsDeviceFixed(editingYgPattern.menu)"
                  @click.stop="toggleFieldPopover('yg-device')"
                >
                  <template v-if="editingYgPattern.device && editingYgPattern.device.length">
                    <span v-for="v in editingYgPattern.device" :key="v" class="notion-tag notion-tag-gray">{{ v }}</span>
                  </template>
                  <span v-else class="notion-select-placeholder">選択してください</span>
                </button>
                <div v-if="activeFieldPopover === 'yg-device'" class="notion-select-panel" @click.stop>
                  <button
                    v-for="v in ygDeviceOptions(editingYgPattern.menu)"
                    :key="v"
                    type="button"
                    class="notion-option-row"
                    @click="toggleYgDevice(v)"
                  >
                    <span class="notion-tag notion-tag-gray">{{ v }}</span>
                    <svg v-if="editingYgPattern.device.includes(v)" class="notion-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg>
                  </button>
                </div>
              </div>
            </div>
            <!-- ⑫ 備考 -->
            <div class="form-group">
              <label class="unified-font"><span class="num">12</span> 備考</label>
              <textarea v-model="editingYgPattern.remarks" class="unified-font custom-textarea transparent-input" placeholder="補足事項があれば入力してください" style="width: 100%; border: 1px solid #ddd; border-radius: 4px; padding: 10px;"></textarea>
            </div>
          </div>
          <div class="modal-footer" style="padding: 15px 20px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #eee;">
            <button type="button" class="back-btn" @click="closeYgEditModal">キャンセル</button>
            <button type="button" class="back-btn modal-confirm-btn" @click="saveYgEditModal">保存する</button>
          </div>
        </div>
      </div>
      <!-- Listing パターン詳細編集モーダル -->
      <div v-if="isListingEditModalOpen" class="modal-overlay" @click.self="closeListingEditModal">
        <div class="modal-content youtube-modal-content" style="max-width: 700px; width: 90%; max-height: 90vh; overflow-y: auto;" @click="activeFieldPopover = null">
          <div class="modal-header">
            <h3>Listing パターン詳細編集</h3>
            <button type="button" class="btn-close" @click="closeListingEditModal">×</button>
          </div>
          <div class="form-body-vertical" style="padding: 20px;">
            <!-- ① メニュー -->
            <div class="form-group">
              <label class="unified-font"><span class="num">1</span> <span class="req">*</span>メニュー</label>
              <select
                v-model="editingListingPattern.menu"
                @change="onListingMenuChange"
                class="matrix-select transparent-input"
                :class="{ 'is-placeholder': !editingListingPattern.menu }"
                style="width: 100%; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;"
              >
                <option value="">選択してください</option>
                <option v-for="m in listingMenuOptions" :key="m" :value="m">{{ m }}</option>
              </select>
            </div>
            <!-- ② 配信期間 -->
            <div class="form-group">
              <label class="unified-font"><span class="num">2</span> 配信期間</label>
              <div style="display: flex; gap: 8px; align-items: center; margin-top: 5px;">
                <input v-model.number="editingListingPattern.periodNumber" type="number" min="1" class="matrix-input transparent-input" placeholder="数値" style="width: 64px; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 8px; text-align: center;">
                <select v-model="editingListingPattern.periodUnit" class="matrix-select transparent-input" style="width: 100px; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
                  <option value="ヶ月間">ヶ月間</option>
                  <option value="日間">日間</option>
                </select>
              </div>
            </div>
            <!-- ③ 予算 -->
            <div class="form-group">
              <label class="unified-font"><span class="num">3</span> 予算</label>
              <input v-model="editingListingBudgetDisplay" type="text" inputmode="numeric" class="matrix-input transparent-input" placeholder="例: ¥500,000" style="width: 100%; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
            </div>
            <!-- ④ エリア -->
            <div class="form-group">
              <label class="unified-font"><span class="num">4</span> エリア</label>
              <div style="display: flex; gap: 10px; margin-top: 5px; align-items: flex-start;">
                <button
                  type="button"
                  class="notion-select-trigger notion-select-trigger-multi"
                  style="flex: 1.4;"
                  @click="openPrefModal('editing-listing')"
                >
                  <template v-if="editingListingPattern.prefNames && editingListingPattern.prefNames.length">
                    <span v-for="name in editingListingPattern.prefNames" :key="name" class="notion-tag notion-tag-gray">{{ name }}</span>
                  </template>
                  <span v-else class="notion-select-placeholder">都道府県を選択</span>
                </button>
                <input
                  v-model="editingListingPattern.city"
                  type="text"
                  class="matrix-input transparent-input"
                  placeholder="市区町村"
                  :disabled="editingListingPattern.prefNames && editingListingPattern.prefNames.includes('全国')"
                  style="flex: 1; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;"
                >
              </div>
            </div>
            <!-- ⑤ 性別 -->
            <div class="form-group">
              <label class="unified-font"><span class="num">5</span> 性別</label>
              <select v-model="editingListingPattern.gender" :disabled="editingListingPattern.menu === 'YSS'" class="matrix-select transparent-input" style="width: 100%; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
                <option value="ALL">ALL</option>
                <option value="男性">男性</option>
                <option value="女性">女性</option>
                <option value="不明">不明</option>
              </select>
            </div>
            <!-- ⑥ 年齢 -->
            <div class="form-group">
              <label class="unified-font"><span class="num">6</span> 年齢</label>
              <div style="display: flex; gap: 10px; margin-top: 5px;">
                <select v-model="editingListingPattern.age1" @change="onListingAge1Change" :disabled="editingListingPattern.menu === 'YSS'" class="matrix-select transparent-input" style="flex: 1; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
                  <option v-for="v in listingAge1Options" :key="v" :value="v">{{ v }}</option>
                </select>
                <select v-model="editingListingPattern.age2" :disabled="isListingAge2Disabled" class="matrix-select transparent-input" :class="{ 'is-placeholder': !editingListingPattern.age2 }" style="flex: 1; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
                  <option v-for="v in listingAge2Options(editingListingPattern.age1)" :key="v" :value="v">{{ v }}</option>
                </select>
                <select v-model="editingListingPattern.age3" :disabled="isListingAge3Disabled" class="matrix-select transparent-input" style="flex: 1; height: 40px; border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;">
                  <option value="">-</option>
                  <option value="不明あり">不明あり</option>
                  <option value="不明なし">不明なし</option>
                </select>
              </div>
            </div>
            <!-- ⑦ デバイス（複数選択） -->
            <div class="form-group">
              <label class="unified-font"><span class="num">7</span> デバイス</label>
              <div class="notion-select-wrap">
                <button
                  type="button"
                  class="notion-select-trigger notion-select-trigger-multi"
                  @click.stop="toggleFieldPopover('listing-device')"
                >
                  <template v-if="editingListingPattern.device && editingListingPattern.device.length">
                    <span v-for="v in editingListingPattern.device" :key="v" class="notion-tag notion-tag-gray">{{ v }}</span>
                  </template>
                  <span v-else class="notion-select-placeholder">選択してください</span>
                </button>
                <div v-if="activeFieldPopover === 'listing-device'" class="notion-select-panel" @click.stop>
                  <button
                    v-for="v in listingDeviceOptions"
                    :key="v"
                    type="button"
                    class="notion-option-row"
                    @click="toggleListingDevice(v)"
                  >
                    <span class="notion-tag notion-tag-gray">{{ v }}</span>
                    <svg v-if="editingListingPattern.device.includes(v)" class="notion-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg>
                  </button>
                </div>
              </div>
            </div>
            <!-- ⑧ 備考 -->
            <div class="form-group">
              <label class="unified-font"><span class="num">8</span> 備考</label>
              <textarea v-model="editingListingPattern.remarks" class="unified-font custom-textarea transparent-input" placeholder="補足事項があれば入力してください" style="width: 100%; border: 1px solid #ddd; border-radius: 4px; padding: 10px;"></textarea>
            </div>
          </div>
          <div class="modal-footer" style="padding: 15px 20px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #eee;">
            <button type="button" class="back-btn" @click="closeListingEditModal">キャンセル</button>
            <button type="button" class="back-btn modal-confirm-btn" @click="saveListingEditModal">保存する</button>
          </div>
        </div>
      </div>
      <!-- YG ターゲティング選択サブモーダル -->
      <div v-if="ygTargetModalOpen" class="modal-overlay" @click.self="closeYgTargetModal">
        <div class="modal-content youtube-modal-content">
          <div class="modal-header"><h3>{{ ygTargetModalTitle }}</h3><button type="button" class="btn-close" @click="closeYgTargetModal">×</button></div>

          <template v-if="ygIsFreeTextTargetingType">
            <textarea
              v-model="ygPendingTargetText"
              class="unified-font custom-textarea transparent-input"
              style="width: 100%; min-height: 160px; border: 1px solid #ddd; border-radius: 6px; padding: 10px;"
              placeholder="内容を入力してください"
            ></textarea>
          </template>
          <template v-else>
            <div class="modal-toolbar"><input v-model="ygTargetSearch" type="search" placeholder="ターゲティングを検索"><button type="button" @click="clearYgTargetSelection">クリア</button></div>
            <div class="modal-selected-header">選択済み</div>
            <div class="modal-selected-body"><span v-for="value in ygPendingTargetNames" :key="value" class="selected-chip">{{ value }} <button type="button" @click="removeYgTarget(value)">×</button></span><span v-if="!ygPendingTargetNames.length" class="empty-selected">未選択</span></div>
            <div class="modal-options-header">候補</div>
            <div class="target-option-list">
              <button
                v-for="value in ygFilteredTargetOptions"
                :key="value"
                type="button"
                class="target-option"
                :class="{ selected: ygPendingTargetNames.includes(value) }"
                @click="toggleYgTarget(value)"
              >
                {{ value }}
              </button>
            </div>
          </template>

          <div class="modal-footer">
            <button type="button" class="back-btn" @click="closeYgTargetModal">キャンセル</button>
            <button type="button" class="back-btn modal-confirm-btn" @click="confirmYgTargetSelection">確定</button>
          </div>
        </div>
      </div>
      <!-- 都道府県モーダル -->
      <div v-if="isModalOpen" class="modal-overlay" @click.self="closePrefModal">
        <div class="modal-content youtube-modal-content">
          <div class="modal-header"><h3>都道府県を選択</h3><button type="button" class="btn-close" @click="closePrefModal">×</button></div>
          <div class="modal-toolbar"><input v-model="prefSearch" type="search" placeholder="都道府県を検索"><button type="button" @click="clearPrefSelection">クリア</button></div>
          <div class="modal-selected-header">選択済み</div>
          <div class="modal-selected-body"><span v-for="name in selectedPrefNames" :key="name" class="selected-chip">{{ name }} <button type="button" @click="removePref(name)">×</button></span><span v-if="!selectedPrefNames.length" class="empty-selected">未選択</span></div>
          <div class="modal-options-header">都道府県</div>
          <div class="pref-grid">
            <button
              v-for="pref in filteredPrefectures"
              :key="pref.id"
              type="button"
              class="pref-option"
              :class="{ selected: selectedPrefNames.includes(pref.name) }"
              @click="togglePref(pref)"
            >
              {{ pref.name }}
            </button>
          </div>
          <div class="modal-footer">
            <button type="button" class="back-btn" @click="closePrefModal">キャンセル</button>
            <button type="button" class="back-btn modal-confirm-btn" @click="confirmPrefSelection">確定</button>
          </div>
        </div>
      </div>
      <!-- ターゲットモーダル -->
      <div v-if="targetModalOpen" class="modal-overlay" @click.self="closeTargetModal">
        <div class="modal-content youtube-modal-content">
          <div class="modal-header"><h3>{{ targetModalTitle }}</h3><button type="button" class="btn-close" @click="closeTargetModal">×</button></div>

          <!-- 自由入力タイプ（カスタムオーディエンス・トピック・コンテンツ・プレースメント） -->
          <template v-if="isFreeTextTargetingType">
            <textarea
              v-model="pendingTargetText"
              class="unified-font custom-textarea transparent-input"
              style="width: 100%; min-height: 160px; border: 1px solid #ddd; border-radius: 6px; padding: 10px;"
              placeholder="内容を入力してください"
            ></textarea>
          </template>

          <!-- カテゴリ一覧タイプ（アフィニティ・インマーケット・詳しいユーザー属性・ライフイベント） -->
          <template v-else>
            <div class="modal-toolbar"><input v-model="targetSearch" type="search" placeholder="ターゲットを検索"><button type="button" @click="clearTargetSelection">クリア</button></div>
            <div class="modal-selected-header">選択済み</div>
            <div class="modal-selected-body"><span v-for="value in selectedTargetValues" :key="value" class="selected-chip">{{ value }} <button type="button" @click="removeTarget(value)">×</button></span><span v-if="!selectedTargetValues.length" class="empty-selected">未選択</span></div>
            <div class="modal-options-header">候補</div>
            <div class="target-option-list">
              <button
                v-for="value in filteredTargetOptions"
                :key="value"
                type="button"
                class="target-option"
                :class="{ selected: selectedTargetValues.includes(value) }"
                @click="toggleTarget(value)"
              >
                {{ value }}
              </button>
            </div>
          </template>

          <div class="modal-footer">
            <button type="button" class="back-btn" @click="closeTargetModal">キャンセル</button>
            <button type="button" class="back-btn modal-confirm-btn" @click="confirmTargetSelection">確定</button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>
<script setup>
import { reactive, computed, ref, watch, onMounted, nextTick } from 'vue'
import { DotLottieVue } from '@lottiefiles/dotlottie-vue'
import PfTable from './PF-Table.vue';
const currentStep = ref(1)
const activeFieldIndex = ref(1)
const hoveredPlatform = ref(null)
const applicantNameInput = ref(null)
const lottieRefs = reactive({})
/* --- 依頼者ログイン（パスワードあり。Email+Password でLogin／Register。Google等は非対応）--- */
const AUTH_STORAGE_KEY = 'simform_logged_in_user'
const authView = ref('login') // 'login' | 'register'
const loggedInUser = ref(null) // { id, name, email }
const isLoggedIn = computed(() => !!loggedInUser.value)
const loginForm = reactive({ email: '', password: '' })
const registerForm = reactive({ lastName: '', firstName: '', email: '', password: '' })
const authError = ref('')
const authLoading = ref(false)

const switchAuthView = (view) => {
  authView.value = view
  authError.value = ''
}

const applyLoggedInUser = (user) => {
  loggedInUser.value = user
  formData.applicantName = user.name
  formData.email = user.email
  try {
    localStorage.setItem(AUTH_STORAGE_KEY, JSON.stringify(user))
  } catch (error) {
    console.error('ログイン情報の保存に失敗しました:', error)
  }
}

const submitLogin = async () => {
  authError.value = ''
  const email = loginForm.email.trim()
  const password = loginForm.password
  if (!email || !password) {
    authError.value = 'メールアドレスとパスワードを入力してください。'
    return
  }
  authLoading.value = true
  try {
    const res = await fetch('/api/requesters/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password })
    })
    const data = await res.json().catch(() => ({}))
    if (!res.ok) {
      authError.value = data.message || 'メールアドレスまたはパスワードが正しくありません。'
      return
    }
    applyLoggedInUser({ id: data.id, name: data.name, email: data.email })
    loginForm.password = ''
  } catch (error) {
    console.error('ログインに失敗しました:', error)
    authError.value = 'ログインに失敗しました。もう一度お試しください。'
  } finally {
    authLoading.value = false
  }
}

const submitRegister = async () => {
  authError.value = ''
  const lastName = registerForm.lastName.trim()
  const firstName = registerForm.firstName.trim()
  const email = registerForm.email.trim()
  const password = registerForm.password
  if (!lastName || !firstName || !email || !password) {
    authError.value = '氏名・メールアドレス・パスワードを入力してください。'
    return
  }
  const name = `${lastName} ${firstName}`
  if (password.length < 8) {
    authError.value = 'パスワードは8文字以上で入力してください。'
    return
  }
  authLoading.value = true
  try {
    const res = await fetch('/api/requesters/register', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name, email, password })
    })
    const data = await res.json().catch(() => ({}))
    if (!res.ok) {
      authError.value = data.message || '登録に失敗しました。もう一度お試しください。'
      return
    }
    applyLoggedInUser({ id: data.id, name: data.name, email: data.email })
    registerForm.password = ''
  } catch (error) {
    console.error('登録に失敗しました:', error)
    authError.value = '登録に失敗しました。もう一度お試しください。'
  } finally {
    authLoading.value = false
  }
}

const logout = () => {
  loggedInUser.value = null
  formData.applicantName = ''
  formData.email = ''
  loginForm.email = ''
  loginForm.password = ''
  authView.value = 'login'
  try {
    localStorage.removeItem(AUTH_STORAGE_KEY)
  } catch (error) {
    console.error('ログアウト処理に失敗しました:', error)
  }
}

const restoreLoggedInUser = () => {
  try {
    const raw = localStorage.getItem(AUTH_STORAGE_KEY)
    if (!raw) return
    const user = JSON.parse(raw)
    if (user && user.email) {
      loggedInUser.value = user
      formData.applicantName = user.name
      formData.email = user.email
    }
  } catch (error) {
    console.error('ログイン情報の復元に失敗しました:', error)
  }
}
const goToPlatformsStep = () => {
  currentStep.value = 2
}
onMounted(() => {
  restoreLoggedInUser()
  if (applicantNameInput.value) {
    applicantNameInput.value.focus()
  }
  document.title = 'SIM FORM'
  const faviconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23111111"><path d="M12 2c.7 4.8 2.9 7 7.7 7.7C14.9 10.4 12.7 12.6 12 17.4c-.7-4.8-2.9-7-7.7-7.7C9.1 9 11.3 6.8 12 2z"/></svg>'
  let faviconLink = document.querySelector("link[rel~='icon']")
  if (!faviconLink) {
    faviconLink = document.createElement('link')
    faviconLink.rel = 'icon'
    document.head.appendChild(faviconLink)
  }
  faviconLink.type = 'image/svg+xml'
  faviconLink.href = `data:image/svg+xml,${faviconSvg}`
})
// 希望納期：日付inputの value/min 用に「YYYY-MM-DD」形式へ変換する
const formatDateInput = (date) => {
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}
// 希望納期の選択下限（今日）。ページを開いた時点の日付で固定する
const todayDateString = formatDateInput(new Date())
// 希望納期のデフォルト値：today + 3日。ただし土日にかかった場合は次の平日（月曜）までスキップする
const getDefaultDueDate = () => {
  const date = new Date()
  date.setDate(date.getDate() + 3)
  while (date.getDay() === 0 || date.getDay() === 6) {
    date.setDate(date.getDate() + 1)
  }
  return formatDateInput(date)
}
// ENTRYデータ
const formData = reactive({
  applicantName: '',
  department: '',
  email: '',
  clientName: '',
  projectName: '',
  dueDate: getDefaultDueDate(),
  remarks: ''
})
/* STEP 3: パターン設定データ */
let youtubePatternSeq = 1
const createEmptyPattern = () => ({
  id: `yt-${Date.now()}-${youtubePatternSeq++}`,
  menu: '',
  placement: [],
  verticalCreative: '',
  horizontalCreative: '',
  periodNumber: 1,
  periodUnit: 'ヶ月',
  budget: null,
  prefId: 'national',
  prefName: '全国',
  prefNames: ['全国'],
  city: '',
  gender: ['all'],
  age1: 'ALL',
  age2: '',
  age3: '不明なし',
  targeting1Type: 'なし',
  targeting1Values: [],
  targeting2Type: 'なし',
  targeting2Values: [],
  targeting3Type: 'なし',
  targeting3Values: [],
  device: ['all'],
  notes: ''
})
// モーダルの状態管理
const isEditModalOpen = ref(false)
const editingIndex = ref(null)
const editingPattern = ref({})
const activeFieldPopover = ref(null)
// 編集モーダルを開く
const openEditModal = (pattern, index) => {
  editingIndex.value = index
  // 選択した行のデータをコピーしてバインド（参照渡しによる予期せぬ即時反映を防ぐため）
  editingPattern.value = JSON.parse(JSON.stringify(pattern))
  activeFieldPopover.value = null
  isEditModalOpen.value = true
}
// 編集モーダルを閉じる
const closeEditModal = () => {
  isEditModalOpen.value = false
  editingIndex.value = null
  editingPattern.value = {}
  activeFieldPopover.value = null
}
// 編集内容を保存する
const saveEditModal = () => {
  if (editingIndex.value !== null && editingIndex.value >= 0 && editingIndex.value < youtubePatterns.length) {
    // spliceを使うことでVueが確実に変更を検知し、テーブルに反映されます
    youtubePatterns.splice(editingIndex.value, 1, JSON.parse(JSON.stringify(editingPattern.value)))
  }
  closeEditModal()
}
const youtubePatterns = reactive([createEmptyPattern()])
/* --- メニュー選択肢（Notion風カラータグ） --- */
const menuOptions = [
  { value: 'VRC2.0（リーチ）', color: 'pink' },
  { value: 'VVC（視聴）', color: 'orange' },
  { value: 'スキップ不可', color: 'red' },
  { value: '目標FQ（マルチフォーマット）', color: 'purple' },
  { value: '目標FQ（スキップ可）', color: 'blue' },
  { value: '目標FQ（スキップ不可）', color: 'green' }
]
const youtubeMenus = menuOptions.map(o => o.value)
const getMenuColor = (menuValue) => {
  const found = menuOptions.find(o => o.value === menuValue)
  return found ? found.color : 'gray'
}
const toggleFieldPopover = (field) => {
  activeFieldPopover.value = activeFieldPopover.value === field ? null : field
}
const selectMenu = (value) => {
  editingPattern.value.menu = value
  // スキップ不可は配信面がインストリームのみのため自動選択し、編集不可にする
  editingPattern.value.placement = value === 'スキップ不可' ? ['インストリーム'] : []
  activeFieldPopover.value = null
  applyYoutubeDeviceLock()
}
const placementOptionsByMenu = {
  'VRC2.0（リーチ）': ['インストリーム', 'バンパー', 'インフィード', 'ショート'],
  'VVC（視聴）': ['インストリーム', 'インフィード', 'ショート'],
  'スキップ不可': ['インストリーム'],
  '目標FQ（マルチフォーマット）': ['インストリーム', 'バンパー', 'インフィード', 'ショート'],
  '目標FQ（スキップ可）': ['インストリーム', 'バンパー'],
  '目標FQ（スキップ不可）': ['インストリーム']
}
const getYoutubePlacements = (menu) => {
  return placementOptionsByMenu[menu] || []
}
// 「スキップ不可」は配信面がインストリーム固定のため、選択・編集不可にする
const isYoutubePlacementLocked = computed(() => {
  return editingPattern.value.menu === 'スキップ不可'
})
const togglePlacementOption = (value) => {
  const list = editingPattern.value.placement
  const idx = list.indexOf(value)
  if (idx >= 0) {
    list.splice(idx, 1)
  } else {
    list.push(value)
  }
}
const verticalCreativeOptions = ['なし', '6秒', '15秒', '30秒', '60秒', '90秒以上']
const horizontalCreativeOptions = ['なし', '6秒', '15秒', '30秒', '60秒', '90秒以上']
/* --- 性別・デバイス選択肢（複数選択・ALL排他制御） --- */
const genderOptions = [
  { value: 'all', label: 'ALL' },
  { value: 'male', label: '男性' },
  { value: 'female', label: '女性' },
  { value: 'unknown', label: '不明' }
]
const genderLabelMap = { all: 'ALL', male: '男性', female: '女性', unknown: '不明' }
const toggleGenderOption = (value) => {
  if (value === 'all') {
    editingPattern.value.gender = ['all']
    return
  }
  let next = editingPattern.value.gender.filter(v => v !== 'all')
  const idx = next.indexOf(value)
  if (idx >= 0) {
    next.splice(idx, 1)
  } else {
    next.push(value)
  }
  const individualValues = ['male', 'female', 'unknown']
  const hasAllIndividuals = individualValues.every(v => next.includes(v))
  if (hasAllIndividuals || next.length === 0) {
    next = ['all']
  }
  editingPattern.value.gender = next
}
const deviceOptions = [
  { value: 'all', label: 'ALL' },
  { value: 'pc', label: 'PC' },
  { value: 'sp', label: 'SP' },
  { value: 'ctv', label: 'CTV' }
]
const deviceLabelMap = { all: 'ALL', pc: 'PC', sp: 'SP', ctv: 'CTV' }
const toggleDeviceOption = (value) => {
  if (value === 'all') {
    editingPattern.value.device = ['all']
    return
  }
  let next = editingPattern.value.device.filter(v => v !== 'all')
  const idx = next.indexOf(value)
  if (idx >= 0) {
    next.splice(idx, 1)
  } else {
    next.push(value)
  }
  if (next.length === 0) {
    next = ['all']
  }
  editingPattern.value.device = next
}
/* --- 「スキップ不可」×広告素材「30秒」の組み合わせはデバイスをCTV限定にする --- */
const isYoutubeDeviceLocked = computed(() => {
  const p = editingPattern.value
  return p.menu === 'スキップ不可' && (p.verticalCreative === '30秒' || p.horizontalCreative === '30秒')
})
const applyYoutubeDeviceLock = () => {
  if (isYoutubeDeviceLocked.value) {
    editingPattern.value.device = ['ctv']
    activeFieldPopover.value = null
  }
}
const onYoutubeCreativeChange = () => {
  applyYoutubeDeviceLock()
}
/* --- 予算：¥1,000,000形式で自動フォーマット --- */
const editingBudgetDisplay = computed({
  get: () => {
    const val = editingPattern.value.budget
    return val ? `¥${Number(val).toLocaleString()}` : ''
  },
  set: (val) => {
    const digits = String(val).replace(/[^\d]/g, '')
    editingPattern.value.budget = digits ? Number(digits) : null
  }
})
/* --- 年齢：①に応じて②③の選択可否・選択肢を制御（①＞②の矛盾を防止） --- */
const age1Options = ['ALL', '18~', '25~', '35~', '45~', '55~', '65~']
const age2Options = (age1) => {
  const all = ['24歳', '34歳', '44歳', '54歳', '64歳']
  if (!age1 || age1 === 'ALL' || age1 === '65~') return all
  const startAge = parseInt(String(age1).replace(/[^0-9]/g, ''), 10)
  return all.filter(v => parseInt(v.replace(/[^0-9]/g, ''), 10) >= startAge)
}
const age3Options = ['不明あり', '不明なし']
const isAge2Disabled = computed(() => {
  return editingPattern.value.age1 === 'ALL' || editingPattern.value.age1 === '65~'
})
const isAge3Disabled = computed(() => {
  return editingPattern.value.age1 === 'ALL'
})
const onAge1Change = () => {
  const pattern = editingPattern.value
  if (pattern.age1 === 'ALL' || pattern.age1 === '65~') {
    pattern.age2 = ''
    return
  }
  const validAges = age2Options(pattern.age1)
  if (pattern.age2 && !validAges.includes(pattern.age2)) {
    pattern.age2 = ''
  }
}
const formatAgeText = (pattern) => {
  if (!pattern.age1 || pattern.age1 === 'ALL') return ''
  let text = pattern.age1
  if (pattern.age1 !== '65~' && pattern.age2) {
    text += pattern.age2
  }
  if (pattern.age1 !== 'ALL' && pattern.age3 === '不明あり') {
    text += '（不明あり）'
  }
  return text
}
/* --- 概要テーブル表示用フォーマット関数 --- */
const formatCreative = (pattern) => {
  const parts = []
  if (pattern.verticalCreative) parts.push(`縦${pattern.verticalCreative}`)
  if (pattern.horizontalCreative) parts.push(`横${pattern.horizontalCreative}`)
  return parts.length ? parts.join(' / ') : '未設定'
}
const formatPeriod = (pattern) => {
  return pattern.periodNumber ? `${pattern.periodNumber}${pattern.periodUnit}` : '未設定'
}
const formatBudget = (pattern) => {
  return pattern.budget ? `¥${Number(pattern.budget).toLocaleString()}` : '未設定'
}
const formatArea = (pattern) => {
  if (!pattern.prefNames || !pattern.prefNames.length) return '未設定'
  const base = pattern.prefNames.join(' / ')
  return pattern.city ? `${base}・${pattern.city}` : base
}
const formatGenderAge = (pattern) => {
  const genderArr = pattern.gender && pattern.gender.length ? pattern.gender : ['all']
  const genderText = genderArr.map(v => genderLabelMap[v] || v).join('/')
  const ageText = formatAgeText(pattern)
  return ageText ? `${genderText} ・ ${ageText}` : genderText
}
const formatGenderOnly = (pattern) => {
  const genderArr = pattern.gender && pattern.gender.length ? pattern.gender : ['all']
  return genderArr.map(v => genderLabelMap[v] || v).join('/')
}
const formatAgeOnly = (pattern) => {
  return formatAgeText(pattern) || 'ALL'
}
const formatTarget = (pattern, n) => {
  const type = pattern[`targeting${n}Type`]
  const values = pattern[`targeting${n}Values`]
  if (!type || type === 'なし') return '未設定'
  if (!values || !values.length) return '未設定'
  if (targetingFreeTextTypes.includes(type)) {
    const text = values[0] || ''
    return text.length > 30 ? text.slice(0, 30) + '...' : text
  }
  return values.join(' / ')
}
const formatDeviceLabel = (pattern) => {
  const deviceArr = pattern.device && pattern.device.length ? pattern.device : ['all']
  return deviceArr.map(v => deviceLabelMap[v] || v).join('/')
}
const prefList = [
  { id: 'national', name: '全国' },
  { id: 'hokkaido', name: '北海道' },
  { id: 'aomori', name: '青森県' },
  { id: 'iwate', name: '岩手県' },
  { id: 'miyagi', name: '宮城県' },
  { id: 'akita', name: '秋田県' },
  { id: 'yamagata', name: '山形県' },
  { id: 'fukushima', name: '福島県' },
  { id: 'ibaraki', name: '茨城県' },
  { id: 'tochigi', name: '栃木県' },
  { id: 'gunma', name: '群馬県' },
  { id: 'saitama', name: '埼玉県' },
  { id: 'chiba', name: '千葉県' },
  { id: 'tokyo', name: '東京都' },
  { id: 'kanagawa', name: '神奈川県' },
  { id: 'niigata', name: '新潟県' },
  { id: 'toyama', name: '富山県' },
  { id: 'ishikawa', name: '石川県' },
  { id: 'fukui', name: '福井県' },
  { id: 'yamanashi', name: '山梨県' },
  { id: 'nagano', name: '長野県' },
  { id: 'gifu', name: '岐阜県' },
  { id: 'shizuoka', name: '静岡県' },
  { id: 'aichi', name: '愛知県' },
  { id: 'mie', name: '三重県' },
  { id: 'shiga', name: '滋賀県' },
  { id: 'kyoto', name: '京都府' },
  { id: 'osaka', name: '大阪府' },
  { id: 'hyogo', name: '兵庫県' },
  { id: 'nara', name: '奈良県' },
  { id: 'wakayama', name: '和歌山県' },
  { id: 'tottori', name: '鳥取県' },
  { id: 'shimane', name: '島根県' },
  { id: 'okayama', name: '岡山県' },
  { id: 'hiroshima', name: '広島県' },
  { id: 'yamaguchi', name: '山口県' },
  { id: 'tokushima', name: '徳島県' },
  { id: 'kagawa', name: '香川県' },
  { id: 'ehime', name: '愛媛県' },
  { id: 'kochi', name: '高知県' },
  { id: 'fukuoka', name: '福岡県' },
  { id: 'saga', name: '佐賀県' },
  { id: 'nagasaki', name: '長崎県' },
  { id: 'kumamoto', name: '熊本県' },
  { id: 'oita', name: '大分県' },
  { id: 'miyazaki', name: '宮崎県' },
  { id: 'kagoshima', name: '鹿児島県' },
  { id: 'okinawa', name: '沖縄県' }
]
const isModalOpen = ref(false)
const selectedRowIndex = ref(null)
const prefSearch = ref('')
const pendingPrefNames = ref([])
const filteredPrefectures = computed(() => {
  const q = prefSearch.value.trim().toLowerCase()
  return q ? prefList.filter(pref => pref.name.toLowerCase().includes(q)) : prefList
})
const selectedPrefNames = computed(() => pendingPrefNames.value)
const resolvePrefTargetRow = (rowIndex) => {
  if (rowIndex === 'editing') return editingPattern.value
  if (rowIndex === 'editing-meta') return editingMetaPattern.value
  if (rowIndex === 'editing-yg') return editingYgPattern.value
  if (rowIndex === 'editing-listing') return editingListingPattern.value
  return youtubePatterns[rowIndex]
}
const openPrefModal = (rowIndex) => {
  selectedRowIndex.value = rowIndex
  const source = resolvePrefTargetRow(rowIndex)
  pendingPrefNames.value = [...(source.prefNames || [])]
  prefSearch.value = ''
  isModalOpen.value = true
}
const closePrefModal = () => {
  isModalOpen.value = false
  selectedRowIndex.value = null
  prefSearch.value = ''
}
const clearPrefSelection = () => {
  pendingPrefNames.value = []
}
const togglePref = (pref) => {
  if (pendingPrefNames.value.includes(pref.name)) {
    removePref(pref.name)
  } else {
    pendingPrefNames.value.push(pref.name)
  }
}
const removePref = (name) => {
  pendingPrefNames.value = pendingPrefNames.value.filter(value => value !== name)
}
const confirmPrefSelection = () => {
  if (selectedRowIndex.value === null) return
  const row = resolvePrefTargetRow(selectedRowIndex.value)
  row.prefNames = [...pendingPrefNames.value]
  row.prefId = row.prefNames[0] || ''
  row.prefName = row.prefNames.join(' / ')
  if (row.prefNames.includes('全国')) {
    row.city = ''
  }
  closePrefModal()
}
/* --- ターゲティングカテゴリ（GASコードを再現） --- */
const targetingTypeOptions = [
  'なし',
  'アフィニティカテゴリ',
  'インマーケットセグメント',
  '詳しいユーザー属性',
  'ライフイベント',
  'カスタムオーディエンス',
  'トピック',
  'コンテンツ',
  'プレースメント'
]
const targetingFreeTextTypes = ['カスタムオーディエンス', 'コンテンツ', 'プレースメント', 'トピック']
const affinityList = [
  "スポーツ、フィットネス","スポーツ、フィットネス>スポーツファン","スポーツ、フィットネス>スポーツファン>アメリカン フットボールのファン","スポーツ、フィットネス>スポーツファン>ウィンター スポーツ ファン","スポーツ、フィットネス>スポーツファン>ウォーター スポーツ ファン","スポーツ、フィットネス>スポーツファン>オーストラリアン フットボールのファン","スポーツ、フィットネス>スポーツファン>オリンピック ファン","スポーツ、フィットネス>スポーツファン>クリケット ファン","スポーツ、フィットネス>スポーツファン>ゴルフファン","スポーツ、フィットネス>スポーツファン>サイクリング ファン","スポーツ、フィットネス>スポーツファン>サッカーファン","スポーツ、フィットネス>スポーツファン>スキーファン","スポーツ、フィットネス>スポーツファン>テニスファン","スポーツ、フィットネス>スポーツファン>バスケットボール ファン","スポーツ、フィットネス>スポーツファン>ボート、ヨットファン","スポーツ、フィットネス>スポーツファン>ホッケーファン","スポーツ、フィットネス>スポーツファン>モーター スポーツ ファン","スポーツ、フィットネス>スポーツファン>ラグビーファン","スポーツ、フィットネス>スポーツファン>ラケットボール ファン","スポーツ、フィットネス>スポーツファン>ランニング ファン","スポーツ、フィットネス>スポーツファン>格闘技、レスリング ファン","スポーツ、フィットネス>スポーツファン>水泳ファン","スポーツ、フィットネス>スポーツファン>野球ファン","スポーツ、フィットネス>健康、フィットネス マニア","スポーツ、フィットネス>健康、フィットネス マニア>ウェイト リフター","スポーツ、フィットネス>健康、フィットネス マニア>ヨガ愛好家",
  "テクノロジー","テクノロジー>ソーシャル メディア ファン","テクノロジー>ハイテク好き","テクノロジー>ハイテク好き>オーディオ好き","テクノロジー>ハイテク好き>クラウド サービスのヘビーユーザー","テクノロジー>ハイテク好き>スマートホーム好き","テクノロジー>ハイテク好き>ハイエンド パソコン好き","テクノロジー>モバイルファン",
  "ニュース、政治","ニュース、政治>ニュース好き","ニュース、政治>ニュース好き>エンターテイメント ニュース好き","ニュース、政治>ニュース好き>ビジネス ニュース好き","ニュース、政治>ニュース好き>ローカル ニュース好き","ニュース、政治>ニュース好き>ワールド ニュース好き","ニュース、政治>ニュース好き>女性向けメディアのファン","ニュース、政治>ニュース好き>政治ニュース好き","ニュース、政治>ニュース好き>男性向けメディアのファン",
  "フード、ダイニング","フード、ダイニング>カフェの常連","フード、ダイニング>グルメ","フード、ダイニング>ファストフード愛好家","フード、ダイニング>ベジタリアン、ビーガン","フード、ダイニング>ベジタリアン、ビーガン>ビーガン","フード、ダイニング>自然食品愛好者","フード、ダイニング>頻繁に外食","フード、ダイニング>頻繁に外食>飲食店（食事時間別）","フード、ダイニング>頻繁に外食>飲食店（食事時間別）>昼食は頻繁に外食","フード、ダイニング>頻繁に外食>飲食店（食事時間別）>朝食は頻繁に外食","フード、ダイニング>頻繁に外食>飲食店（食事時間別）>夕食は頻繁に外食","フード、ダイニング>料理愛好家","フード、ダイニング>料理愛好家>簡単料理研究家","フード、ダイニング>料理愛好家>料理研究家",
  "メディア、エンターテイメント","メディア、エンターテイメント>ゲームファン","メディア、エンターテイメント>ゲームファン>e スポーツファン","メディア、エンターテイメント>ゲームファン>PC ゲーマー","メディア、エンターテイメント>ゲームファン>アクション ゲーム ファン","メディア、エンターテイメント>ゲームファン>アドベンチャー ゲーム、ウォー ゲーム ファン","メディア、エンターテイメント>ゲームファン>カジュアル ゲーム、ソーシャル ゲーム ファン","メディア、エンターテイメント>ゲームファン>ゲームマニア","メディア、エンターテイメント>ゲームファン>ゲーム機のゲーマー","メディア、エンターテイメント>ゲームファン>シューティング ゲーム ファン","メディア、エンターテイメント>ゲームファン>スポーツ ゲーム ファン","メディア、エンターテイメント>ゲームファン>ドライビング ゲーム、レース ゲーム ファン","メディア、エンターテイメント>ゲームファン>ロールプレイング ゲーム ファン","メディア、エンターテイメント>ゲームファン>新作および近日発売予定のビデオゲームのファン","メディア、エンターテイメント>コミック、アニメーション ファン","メディア、エンターテイメント>テレビっ子","メディア、エンターテイメント>テレビっ子>SF 番組、ファンタジー番組ファン","メディア、エンターテイメント>テレビっ子>お笑い番組ファン","メディア、エンターテイメント>テレビっ子>テレビドラマ ファン","メディア、エンターテイメント>テレビっ子>ドキュメンタリー番組、ノンフィクション番組ファン","メディア、エンターテイメント>テレビっ子>家族向け番組ファン","メディア、エンターテイメント>テレビっ子>生中継、リアリティ番組、トーク番組ファン","メディア、エンターテイメント>テレビのライトユーザー","メディア、エンターテイメント>ミュージック ファン","メディア、エンターテイメント>ミュージック ファン>インディーズ ロック、オルタナティブ ロック ファン","メディア、エンターテイメント>ミュージック ファン>エレクトロニック ダンス ミュージック ファン","メディア、エンターテイメント>ミュージック ファン>カントリー ミュージック ファン","メディア、エンターテイメント>ミュージック ファン>クラシック音楽ファン","メディア、エンターテイメント>ミュージック ファン>ジャズファン","メディア、エンターテイメント>ミュージック ファン>フォーク、伝統音楽ファン","メディア、エンターテイメント>ミュージック ファン>ブルースファン","メディア、エンターテイメント>ミュージック ファン>ヘビーメタル ファン","メディア、エンターテイメント>ミュージック ファン>ポップ ミュージック ファン","メディア、エンターテイメント>ミュージック ファン>ラップ、ヒップホップ ファン","メディア、エンターテイメント>ミュージック ファン>ラテン音楽のファン","メディア、エンターテイメント>ミュージック ファン>ロック ミュージック ファン","メディア、エンターテイメント>ミュージック ファン>ワールド ミュージック ファン","メディア、エンターテイメント>映画ファン","メディア、エンターテイメント>映画ファン>SF 映画、ファンタジー映画ファン","メディア、エンターテイメント>映画ファン>アクション映画、アドベンチャー映画ファン","メディア、エンターテイメント>映画ファン>コメディ映画ファン","メディア、エンターテイメント>映画ファン>ファミリー映画ファン","メディア、エンターテイメント>映画ファン>ホラー映画ファン","メディア、エンターテイメント>映画ファン>新作および近日公開予定の映画のファン","メディア、エンターテイメント>映画ファン>南アジア映画のファン","メディア、エンターテイメント>映画ファン>恋愛映画、ドラマ映画ファン","メディア、エンターテイメント>読書好き",
  "ライフスタイル、趣味","ライフスタイル、趣味>アウトドア ファン","ライフスタイル、趣味>アマチュア カメラマン","ライフスタイル、趣味>エコ生活愛好者","ライフスタイル、趣味>バー、ナイトクラブ好き","ライフスタイル、趣味>ビジネスのプロフェッショナル","ライフスタイル、趣味>ファッショニスタ","ライフスタイル、趣味>ペット愛好者","ライフスタイル、趣味>ペット愛好者>犬好き","ライフスタイル、趣味>ペット愛好者>猫好き","ライフスタイル、趣味>ライブイベントに頻繁に参加","ライフスタイル、趣味>演劇ファン","ライフスタイル、趣味>家族向け","ライフスタイル、趣味>家族向け>ホームスクーリングを選択する保護者","ライフスタイル、趣味>慈善事業の寄付者、ボランティア","ライフスタイル、趣味>冒険好き",
  "家庭、園芸","家庭、園芸>DIY 愛好者","家庭、園芸>インテリア好き",
  "銀行、金融","銀行、金融>オンライン銀行を利用","銀行、金融>投資マニア",
  "乗り物、交通機関","乗り物、交通機関>交通手段","乗り物、交通機関>交通手段>タクシーの利用者","乗り物、交通機関>交通手段>公共交通機関の利用者","乗り物、交通機関>自動車ファン","乗り物、交通機関>自動車ファン>オートバイ ファン","乗り物、交通機関>自動車ファン>トラック、SUV ファン","乗り物、交通機関>自動車ファン>高性能車ファン、高級車ファン",
  "買い物好き","買い物好き（店舗タイプ別）","買い物好き>バーゲン愛好家","買い物好き>高級ブランド好き","買い物好き>節約好き","買い物好き>買い物マニア","買い物好き>買い物好き（店舗タイプ別）>買い物好き（コンビニエンス ストア）","買い物好き>買い物好き（店舗タイプ別）>買い物好き（スーパーマーケット）","買い物好き>買い物好き（店舗タイプ別）>買い物好き（デパート）","買い物好き>買い物好き（店舗タイプ別）>買い物好き（食料品店）",
  "美容、健康","美容、健康>美容通","美容、健康>頻繁にサロンを訪問",
  "旅行","旅行>出張の多い人","旅行>旅行好き","旅行>旅行好き>旅行好き（スノーリゾート）","旅行>旅行好き>旅行好き（ビーチリゾート）","旅行>旅行好き>旅行好き（家族旅行）","旅行>旅行好き>旅行好き（豪華旅行）"
]
const detailedDemographicsList = [
  "教育>現役の大学生","教育>最終学歴>学士号","教育>最終学歴>高校卒","教育>最終学歴>大学院卒",
  "子供の有無>子供あり>子供あり（0～1 歳の乳児）","子供の有無>子供あり>子供あり（1～3 歳の幼児）","子供の有無>子供あり>子供あり（4～5 歳の幼稚園児）","子供の有無>子供あり>子供あり（6～12 歳の小学生）","子供の有無>子供あり>子供あり（13～17 歳）",
  "就業状況>業種>サービス業","就業状況>業種>テクノロジー業界","就業状況>業種>ヘルスケア業界","就業状況>業種>教育機関","就業状況>業種>金融業界","就業状況>業種>建設業","就業状況>業種>製造業","就業状況>業種>不動産業界",
  "就業状況>社員数>小規模雇用者（従業員数: 1～249 人）","就業状況>社員数>大規模雇用者（従業員数: 250～10,000 人）","就業状況>社員数>超大規模雇用者（従業員数: 10,000 人以上）",
  "住宅所有状況>住宅所有","住宅所有状況>賃貸",
  "配偶者の有無>既婚","配偶者の有無>交際中","配偶者の有無>独身"
]
const lifeEventsList = [
  "マイホームの購入","マイホームの購入>マイホームを近々購入予定","マイホームの購入>マイホームを最近購入",
  "引越し","引越し>引越し予定","引越し>最近引越した",
  "起業","起業>近々起業予定","起業>最近起業した",
  "結婚","結婚>結婚予定","結婚>最近結婚した",
  "自宅のリフォーム","自宅のリフォーム>自宅を近々リフォーム予定","自宅のリフォーム>自宅を最近リフォーム",
  "新しいペット","新しいペット>近々犬を飼い始める予定","新しいペット>近々猫を飼い始める予定","新しいペット>最近犬を飼い始めた","新しいペット>最近猫を飼い始めた",
  "大学卒","大学卒>最近卒業した","大学卒>卒業予定",
  "定年退職","定年退職>近々退職予定","定年退職>最近退職した",
  "転職","転職>近々転職予定","転職>最近転職した"
]
const inMarketList = [
  "アート、工芸用品",
  "アパレル、アクセサリ","アパレル、アクセサリ>アウター","アパレル、アクセサリ>コスチューム","アパレル、アクセサリ>スポーツウェア","アパレル、アクセサリ>スポーツウェア>ヨガウェア","アパレル、アクセサリ>スポーツウェア>ランニング ウェア","アパレル、アクセサリ>ハンドバッグ","アパレル、アクセサリ>下着","アパレル、アクセサリ>眼鏡","アパレル、アクセサリ>眼鏡>サングラス","アパレル、アクセサリ>眼鏡>眼鏡、コンタクト レンズ","アパレル、アクセサリ>靴","アパレル、アクセサリ>靴>スポーツ シューズ","アパレル、アクセサリ>靴>ドレス シューズ","アパレル、アクセサリ>靴>ブーツ","アパレル、アクセサリ>靴下","アパレル、アクセサリ>財布、ブリーフケース、革製品","アパレル、アクセサリ>女性用下着","アパレル、アクセサリ>紳士服","アパレル、アクセサリ>水着","アパレル、アクセサリ>婦人服","アパレル、アクセサリ>宝石、時計","アパレル、アクセサリ>宝石、時計>結婚指輪、婚約指輪","アパレル、アクセサリ>宝石、時計>高級ジュエリー　","アパレル、アクセサリ>宝石、時計>高級ジュエリー　>ネックレス","アパレル、アクセサリ>宝石、時計>時計","アパレル、アクセサリ>旅行バッグ","アパレル、アクセサリ>礼服","アパレル、アクセサリ>礼服>ビジネス スーツ","アパレル、アクセサリ>礼服>ブライダル衣装",
  "イベントのチケット","イベントのチケット>コンサート、音楽祭のチケット","イベントのチケット>スポーツのチケット","イベントのチケット>スポーツのチケット>アメリカン フットボールのチケット","イベントのチケット>スポーツのチケット>サッカーのチケット","イベントのチケット>スポーツのチケット>バスケットボールのチケット","イベントのチケット>スポーツのチケット>ホッケーのチケット","イベントのチケット>スポーツのチケット>野球のチケット","イベントのチケット>舞台芸術のチケット","イベントのチケット>舞台芸術のチケット>ブロードウェイ、劇場のチケット",
  "ギフト、行事","ギフト、行事>パーソナライズド ギフト","ギフト、行事>パーティー用品、イベント企画","ギフト、行事>パーティー用品、イベント企画>イベント企画","ギフト、行事>パーティー用品、イベント企画>パーティー用品","ギフト、行事>フラワーギフト","ギフト、行事>詰め合わせギフト","ギフト、行事>挙式、披露宴プラン","ギフト、行事>写真サービス、スタジオ、ビデオ撮影","ギフト、行事>写真サービス、スタジオ、ビデオ撮影>イベント カメラマン、スタジオ","ギフト、行事>写真サービス、スタジオ、ビデオ撮影>写真の印刷サービス","ギフト、行事>祝祭日用のアイテムや飾り","ギフト、行事>祝祭日用のアイテムや飾り>クリスマス用品、飾り","ギフト、行事>祝祭日用のアイテムや飾り>バレンタイン用品、飾り","ギフト、行事>祝祭日用のアイテムや飾り>ハロウィン用品、飾り",
  "コンピュータ、周辺機器","コンピュータ、周辺機器>コンピュータ","コンピュータ、周辺機器>コンピュータ>タブレット、ポータブル デバイス","コンピュータ、周辺機器>コンピュータ>デスクトップ パソコン","コンピュータ、周辺機器>コンピュータ>ラップトップ、ノートパソコン","コンピュータ、周辺機器>コンピュータ用アクセサリ、部品","コンピュータ、周辺機器>コンピュータ用アクセサリ、部品>コンピュータ モニター","コンピュータ、周辺機器>コンピュータ用アクセサリ、部品>メモリ、ストレージ","コンピュータ、周辺機器>プリンタ、スキャナ、FAX",
  "スポーツ、フィットネス","スポーツ、フィットネス>アウトドア用品","スポーツ、フィットネス>アウトドア用品>キャンプ、ハイキング用品","スポーツ、フィットネス>アウトドア用品>釣り用品","スポーツ、フィットネス>スポーツ用品","スポーツ、フィットネス>スポーツ用品>ウィンター スポーツ用品","スポーツ、フィットネス>スポーツ用品>ウォーター スポーツ用品","スポーツ、フィットネス>スポーツ用品>ゴルフ用品","スポーツ、フィットネス>スポーツ用品>サッカー用品","スポーツ、フィットネス>スポーツ用品>スケートボード用品","スポーツ、フィットネス>スポーツ用品>ホッケー用品","スポーツ、フィットネス>スポーツ用品>野球用品","スポーツ、フィットネス>フィットネス商品、サービス","スポーツ、フィットネス>フィットネス商品、サービス>ジム、アスレチック クラブ","スポーツ、フィットネス>フィットネス商品、サービス>フィットネス クラス、個別トレーニング サービス","スポーツ、フィットネス>フィットネス商品、サービス>フィットネス クラス、個別トレーニング サービス>オンライン フィットネス クラス","スポーツ、フィットネス>フィットネス商品、サービス>フィットネス器具","スポーツ、フィットネス>フィットネス商品、サービス>フィットネス器具>ウェイト トレーニング器具","スポーツ、フィットネス>フィットネス商品、サービス>フィットネス器具>フィットネス商品","スポーツ、フィットネス>フィットネス商品、サービス>フィットネス器具>有酸素トレーニング器具",
  "ソフトウェア","ソフトウェア>アンチウィルス、セキュリティ ソフトウェア","ソフトウェア>オーディオ、音楽ソフトウェア","ソフトウェア>オフィス、ビジネスソフトウェア","ソフトウェア>デザイン ソフトウェア","ソフトウェア>デザイン ソフトウェア>写真編集ソフトウェア","ソフトウェア>デザイン ソフトウェア>描画、アニメーション ソフトウェア","ソフトウェア>ビデオチャット ソフトウェア","ソフトウェア>会計ソフトウェア","ソフトウェア>動画の編集や制作用ソフトウェア",
  "デートサービス",
  "ビジネス サービス","ビジネス サービス>ビジネス テクノロジー","ビジネス サービス>ビジネス テクノロジー>ウェブ サービス","ビジネス サービス>ビジネス テクノロジー>ウェブ サービス>ウェブ ホスティング","ビジネス サービス>ビジネス テクノロジー>ウェブ サービス>ウェブデザイン、開発","ビジネス サービス>ビジネス テクノロジー>ウェブ サービス>ドメイン登録","ビジネス サービス>ビジネス テクノロジー>ネットワーク システム、サービス","ビジネス サービス>ビジネス テクノロジー>ネットワーク システム、サービス>ネットワーク、エンタープライズ セキュリティ","ビジネス サービス>ビジネス テクノロジー>ネットワーク システム、サービス>ネットワーク管理","ビジネス サービス>ビジネス テクノロジー>ネットワーク システム、サービス>ネットワーク装置、仮想化","ビジネス サービス>ビジネス テクノロジー>ネットワーク システム、サービス>ホスト データ、クラウド ストレージ","ビジネス サービス>ビジネス テクノロジー>企業ソフトウェア","ビジネス サービス>ビジネス テクノロジー>企業ソフトウェア>CRM ソリューション","ビジネス サービス>ビジネス テクノロジー>企業ソフトウェア>ERP ソリューション","ビジネス サービス>ビジネス テクノロジー>企業ソフトウェア>コラボレーション、会議ツール","ビジネス サービス>ビジネス テクノロジー>企業ソフトウェア>ヘルプデスク、カスタマー サポート ソリューション","ビジネス サービス>ビジネス印刷、ドキュメント サービス","ビジネス サービス>ビジネス金融サービス","ビジネス サービス>企業イベント企画","ビジネス サービス>給与サービス","ビジネス サービス>広告、マーケティング サービス","ビジネス サービス>広告、マーケティング サービス>SEO、SEM サービス","ビジネス サービス>広告、マーケティング サービス>電子メールによるマーケティング サービス","ビジネス サービス>支払い処理、決済処理サービス","ビジネス サービス>事務用品","ビジネス サービス>事務用品>オフィス家具","ビジネス サービス>人材サービス、リクルート サービス","ビジネス サービス>物理的セキュリティとアクセス制御",
  "ビジネス、産業向けの関連商品","ビジネス、産業向けの関連商品>運搬管理設備","ビジネス、産業向けの関連商品>看板、標識","ビジネス、産業向けの関連商品>作業用安全保護具","ビジネス、産業向けの関連商品>厨房設備",
  "メディア、エンターテイメント","メディア、エンターテイメント>DVD、ビデオ","メディア、エンターテイメント>テレビや動画のストリーミング サービス","メディア、エンターテイメント>ビデオゲーム","メディア、エンターテイメント>ビデオゲーム ストリーミング サービス","メディア、エンターテイメント>ボードゲーム","メディア、エンターテイメント>音声ストリーミング サービス","メディア、エンターテイメント>書籍",
  "家庭、園芸","家庭、園芸>ペット用品","家庭、園芸>ホーム セキュリティ","家庭、園芸>ホームデコレーション","家庭、園芸>ホームデコレーション>カーテン、窓装飾品","家庭、園芸>ホームデコレーション>カーテン、窓装飾品>カーテン","家庭、園芸>ホームデコレーション>カーテン、窓装飾品>ブラインド、シェード","家庭、園芸>ホームデコレーション>ベッド用品","家庭、園芸>ホームデコレーション>ラグ、カーペット","家庭、園芸>ホームデコレーション>リネン","家庭、園芸>ホームデコレーション>照明、備品","家庭、園芸>ホームデコレーション>照明、備品>天井用の照明装置","家庭、園芸>ホームデコレーション>照明、備品>壁掛けの照明装置","家庭、園芸>ホームデコレーション>暖炉","家庭、園芸>ホームデコレーション>美術","家庭、園芸>リフォーム","家庭、園芸>リフォーム>キッチン カウンター、浴室カウンター","家庭、園芸>リフォーム>ツール","家庭、園芸>リフォーム>ツール>測定器、センサー","家庭、園芸>リフォーム>フローリング","家庭、園芸>リフォーム>食器棚、浴室キャビネット","家庭、園芸>リフォーム>電源、電気供給","家庭、園芸>リフォーム>塗装","家庭、園芸>リフォーム>配管設備","家庭、園芸>リフォーム>配管設備用の機器、部品","家庭、園芸>屋外用アイテム","家庭、園芸>屋外用アイテム>バーベキュー、グリル","家庭、園芸>屋外用アイテム>バーベキュー、グリル用品","家庭、園芸>屋外用アイテム>プール、温泉","家庭、園芸>屋外用アイテム>芝刈り機","家庭、園芸>屋外用アイテム>芝生の手入れ、ガーデニング","家庭、園芸>屋外用アイテム>庭、アウトドア用家具","家庭、園芸>屋外用アイテム>庭、アウトドア用家具>アウトドア家具一式","家庭、園芸>屋外用アイテム>物置小屋、屋外構築物","家庭、園芸>家具","家庭、園芸>家具>キッチン、ダイニング","家庭、園芸>家具>キッチン、ダイニング>キッチン テーブル、ダイニング テーブル","家庭、園芸>家具>キッチン、ダイニング>スツール","家庭、園芸>家具>キッチン、ダイニング>食卓用椅子、ダイニング チェア","家庭、園芸>家具>リビング","家庭、園芸>家具>家庭向け収納整理用品","家庭、園芸>家具>在宅ワーク","家庭、園芸>家具>在宅ワーク>オフィスチェア","家庭、園芸>家具>在宅ワーク>デスク","家庭、園芸>家具>寝室","家庭、園芸>家具>寝室>ベッド、ベッドフレーム","家庭、園芸>家具>寝室>マットレス","家庭、園芸>家具>託児所","家庭、園芸>家庭用品","家庭、園芸>家庭用品>家庭向け掃除用品","家庭、園芸>家庭用品>害虫、害獣駆除用品","家庭、園芸>家庭用品>手指用除菌剤","家庭、園芸>住居、庭に関するサービス","家庭、園芸>住居、庭に関するサービス>インテリア デザイン、装飾に関するサービス","家庭、園芸>住居、庭に関するサービス>カーペットの張り替え","家庭、園芸>住居、庭に関するサービス>ドア、窓の取り付け","家庭、園芸>住居、庭に関するサービス>フローリング サービス","家庭、園芸>住居、庭に関するサービス>ホーム クリーニング サービス","家庭、園芸>住居、庭に関するサービス>一般的な請負、リフォーム サービス","家庭、園芸>住居、庭に関するサービス>屋根ふきサービス","家庭、園芸>住居、庭に関するサービス>家屋の検査サービス","家庭、園芸>住居、庭に関するサービス>害虫・害獣駆除サービス","家庭、園芸>住居、庭に関するサービス>建築関連のサービス","家庭、園芸>住居、庭に関するサービス>鍵サービス","家庭、園芸>住居、庭に関するサービス>芝生、庭メンテナンス","家庭、園芸>住居、庭に関するサービス>造園設計","家庭、園芸>住居、庭に関するサービス>電気工事","家庭、園芸>住居、庭に関するサービス>塗装サービス","家庭、園芸>住居、庭に関するサービス>配管サービス","家庭、園芸>浄水器","家庭、園芸>食器","家庭、園芸>生活家電用品","家庭、園芸>生活家電用品>キッチン用品","家庭、園芸>生活家電用品>キッチン用品>コーヒー メーカー、エスプレッソ マシンの関連用品","家庭、園芸>生活家電用品>キッチン用品>ミキサー、ブレンダーの関連用品","家庭、園芸>生活家電用品>キッチン用品>冷蔵庫の関連用品","家庭、園芸>生活家電用品>温度調整機、空調設備","家庭、園芸>生活家電用品>温度調整機、空調設備>エアコン","家庭、園芸>生活家電用品>小型家電","家庭、園芸>生活家電用品>小型家電>コーヒー メーカー、エスプレッソ マシン","家庭、園芸>生活家電用品>小型家電>ジューサー、ブレンダー","家庭、園芸>生活家電用品>小型家電>ミキサー","家庭、園芸>生活家電用品>食器洗い機","家庭、園芸>生活家電用品>洗濯機、乾燥機","家庭、園芸>生活家電用品>掃除機","家庭、園芸>生活家電用品>調理レンジ、コンロ","家庭、園芸>生活家電用品>電子レンジ","家庭、園芸>生活家電用品>冷蔵庫","家庭、園芸>調理器具、製菓道具",
  "家電","家電>オーディオ","家電>オーディオ>カーオーディオ","家電>オーディオ>ステレオ システム","家電>オーディオ>スピーカー","家電>オーディオ>プロ ミュージシャン、DJ 向け機器","家電>オーディオ>ヘッドフォン、ヘッドセット","家電>カメラ","家電>カメラ>カメラ レンズ","家電>カメラ>デジタル一眼レフ カメラ","家電>ゲーム機","家電>ゲーム機>Xbox","家電>ゲーム機>プレイステーション","家電>ゲーム機>任天堂のゲーム機","家電>ゲーム用周辺機器、関連用品","家電>テレビ","家電>ビデオカメラ","家電>ホームシアター システム","家電>携帯電話","家電>携帯電話>Android 搭載スマートフォン","家電>携帯電話>iOS 搭載スマートフォン","家電>携帯電話用アクセサリ","家電>電源アダプタ、充電器","家電>電池",
  "楽器、音楽の関連用品",
  "季節的な買い物","季節的な買い物>クリスマスの買い物","季節的な買い物>クリスマスの買い物>クリスマス後のセールで買い物","季節的な買い物>クリスマスの買い物>クリスマス用品の買い物（オンライン）","季節的な買い物>クリスマスの買い物>クリスマス用品の買い物（実店舗）","季節的な買い物>ブラック フライデーに買い物","季節的な買い物>ブラック フライデーに買い物>ブラック フライデーにオンラインで買い物","季節的な買い物>ブラック フライデーに買い物>ブラック フライデーに実店舗で買い物","季節的な買い物>新学期に向けた買い物","季節的な買い物>新学期に向けた買い物>学用品","季節的な買い物>新学期に向けた買い物>新学期に向けた衣服、小物","季節的な買い物>母の日の買い物","季節的な買い物>母の日の買い物>母の日の花やカード","季節的な買い物>母の日の買い物>母の日の外食",
  "教育","教育>外国語学習","教育>教育リソース（小学生向け）","教育>教育リソース（中学生、高校生向け）","教育>教育リソース（保育園児、幼稚園児向け）","教育>公開オンライン講座","教育>受験対策、個別指導","教育>小中学校、高等学校","教育>大学、短期大学","教育>大学、短期大学>アート、デザイン","教育>大学、短期大学>テクノロジー","教育>大学、短期大学>ビジネス スクール","教育>大学、短期大学>医療教育","教育>大学、短期大学>医療教育>看護教育","教育>大学、短期大学>美容、コスメ","教育>留学",
  "金融サービス","金融サービス>クレジット、融資","金融サービス>クレジット、融資>クレジット カード","金融サービス>クレジット、融資>学生ローン","金融サービス>クレジット、融資>個人ローン","金融サービス>クレジット、融資>事業融資","金融サービス>クレジット、融資>自動車ローン","金融サービス>クレジット、融資>住宅ローン","金融サービス>クレジット、融資>住宅ローン>住宅ローンの借り換え","金融サービス>クレジット、融資>住宅ローン>住宅購入ローン","金融サービス>クレジット、融資>住宅ローン>住宅担保ローン","金融サービス>クレジット、融資>信用調査","金融サービス>ファイナンシャル プランニング","金融サービス>ファイナンシャル プランニング>セカンドライフ プランニング","金融サービス>ファイナンシャル プランニング>遺産管理","金融サービス>銀行サービス","金融サービス>銀行サービス>デビットカード、小切手","金融サービス>銀行サービス>貯蓄口座","金融サービス>投資サービス","金融サービス>納税申告サービスおよびソフトウェア","金融サービス>保険","金融サービス>保険>健康保険","金融サービス>保険>自動車保険","金融サービス>保険>住宅保険","金融サービス>保険>生命保険","金融サービス>保険>旅行保険",
  "自動車、乗り物","自動車、乗り物>自動車","自動車、乗り物>自動車>自動車（メーカー別）","自動車、乗り物>自動車>自動車（メーカー別）>Alfa Romeo","自動車、乗り物>自動車>自動車（メーカー別）>BMW","自動車、乗り物>自動車>自動車（メーカー別）>Hyundai","自動車、乗り物>自動車>自動車（メーカー別）>Ram Trucks","自動車、乗り物>自動車>自動車（メーカー別）>SEAT","自動車、乗り物>自動車>自動車（メーカー別）>Tesla Motors","自動車、乗り物>自動車>自動車（メーカー別）>アウディ","自動車、乗り物>自動車>自動車（メーカー別）>アキュラ","自動車、乗り物>自動車>自動車（メーカー別）>いすゞ","自動車、乗り物>自動車>自動車（メーカー別）>インフィニティ","自動車、乗り物>自動車>自動車（メーカー別）>キア","自動車、乗り物>自動車>自動車（メーカー別）>キャデラック","自動車、乗り物>自動車>自動車（メーカー別）>クライスラー","自動車、乗り物>自動車>自動車（メーカー別）>サイオン","自動車、乗り物>自動車>自動車（メーカー別）>ジープ","自動車、乗り物>自動車>自動車（メーカー別）>シトロエン","自動車、乗り物>自動車>自動車（メーカー別）>シボレー","自動車、乗り物>自動車>自動車（メーカー別）>ジャガー","自動車、乗り物>自動車>自動車（メーカー別）>スズキ","自動車、乗り物>自動車>自動車（メーカー別）>スバル","自動車、乗り物>自動車>自動車（メーカー別）>ゼネラル モーターズ","自動車、乗り物>自動車>自動車（メーカー別）>ダッジ","自動車、乗り物>自動車>自動車（メーカー別）>トヨタ","自動車、乗り物>自動車>自動車（メーカー別）>ビュイック","自動車、乗り物>自動車>自動車（メーカー別）>フィアット","自動車、乗り物>自動車>自動車（メーカー別）>フォード","自動車、乗り物>自動車>自動車（メーカー別）>フォルクスワーゲン","自動車、乗り物>自動車>自動車（メーカー別）>プジョー","自動車、乗り物>自動車>自動車（メーカー別）>ボクスホール オペル","自動車、乗り物>自動車>自動車（メーカー別）>ポルシェ","自動車、乗り物>自動車>自動車（メーカー別）>ボルボ","自動車、乗り物>自動車>自動車（メーカー別）>ホンダ","自動車、乗り物>自動車>自動車（メーカー別）>マセラティ","自動車、乗り物>自動車>自動車（メーカー別）>マツダ","自動車、乗り物>自動車>自動車（メーカー別）>ミニ","自動車、乗り物>自動車>自動車（メーカー別）>メルセデス ベンツ","自動車、乗り物>自動車>自動車（メーカー別）>ランドローバー","自動車、乗り物>自動車>自動車（メーカー別）>リンカーン","自動車、乗り物>自動車>自動車（メーカー別）>ルノー","自動車、乗り物>自動車>自動車（メーカー別）>レクサス","自動車、乗り物>自動車>自動車（メーカー別）>三菱","自動車、乗り物>自動車>自動車（メーカー別）>日産","自動車、乗り物>自動車>自動車（種類別）","自動車、乗り物>自動車>自動車（種類別）>SUV","自動車、乗り物>自動車>自動車（種類別）>SUV>SUV（新車）","自動車、乗り物>自動車>自動車（種類別）>SUV>SUV（中古車）","自動車、乗り物>自動車>自動車（種類別）>オートバイ","自動車、乗り物>自動車>自動車（種類別）>オートバイ>オートバイ（新車）","自動車、乗り物>自動車>自動車（種類別）>オートバイ>バイク（中古車）","自動車、乗り物>自動車>自動車（種類別）>オープンカー","自動車、乗り物>自動車>自動車（種類別）>オープンカー>オープンカー（新車）","自動車、乗り物>自動車>自動車（種類別）>オープンカー>オープンカー（中古車）","自動車、乗り物>自動車>自動車（種類別）>オフロード車","自動車、乗り物>自動車>自動車（種類別）>オフロード車>オフロード車（新車）","自動車、乗り物>自動車>自動車（種類別）>オフロード車>オフロード車（中古車）","自動車、乗り物>自動車>自動車（種類別）>クーペ","自動車、乗り物>自動車>自動車（種類別）>クーペ>クーペ（新車）","自動車、乗り物>自動車>自動車（種類別）>クーペ>クーペ（中古車）","自動車、乗り物>自動車>自動車（種類別）>クラシック カー","自動車、乗り物>自動車>自動車（種類別）>クロスオーバー","自動車、乗り物>自動車>自動車（種類別）>クロスオーバー>クロスオーバー（新車）","自動車、乗り物>自動車>自動車（種類別）>クロスオーバー>クロスオーバー（中古車）","自動車、乗り物>自動車>自動車（種類別）>コンパクト カー","自動車、乗り物>自動車>自動車（種類別）>コンパクト カー>コンパクト カー（新車）","自動車、乗り物>自動車>自動車（種類別）>コンパクト カー>コンパクト カー（中古車）","自動車、乗り物>自動車>自動車（種類別）>スクーター、モペット","自動車、乗り物>自動車>自動車（種類別）>スクーター、モペット>スクーター、モペット（新車）","自動車、乗り物>自動車>自動車（種類別）>スクーター、モペット>スクーター、モペット（中古車）","自動車、乗り物>自動車>自動車（種類別）>ステーション ワゴン","自動車、乗り物>自動車>自動車（種類別）>ステーション ワゴン>ステーション ワゴン（新車）","自動車、乗り物>自動車>自動車（種類別）>ステーション ワゴン>ステーション ワゴン（中古車）","自動車、乗り物>自動車>自動車（種類別）>スポーツ カー","自動車、乗り物>自動車>自動車（種類別）>スポーツ カー>スポーツカー（新車）","自動車、乗り物>自動車>自動車（種類別）>スポーツ カー>スポーツカー（中古車）","自動車、乗り物>自動車>自動車（種類別）>セダン","自動車、乗り物>自動車>自動車（種類別）>セダン>セダン（新車）","自動車、乗り物>自動車>自動車（種類別）>セダン>セダン（中古車）","自動車、乗り物>自動車>自動車（種類別）>ディーゼル車","自動車、乗り物>自動車>自動車（種類別）>ディーゼル車>ディーゼル車（新車）","自動車、乗り物>自動車>自動車（種類別）>ディーゼル車>ディーゼル車（中古車）","自動車、乗り物>自動車>自動車（種類別）>ハイブリッド車、代替燃料車","自動車、乗り物>自動車>自動車（種類別）>ハイブリッド車、代替燃料車>ハイブリッド車、代替燃料車（新車）","自動車、乗り物>自動車>自動車（種類別）>ハイブリッド車、代替燃料車>ハイブリッド車、代替燃料車（中古車）","自動車、乗り物>自動車>自動車（種類別）>ハッチバック","自動車、乗り物>自動車>自動車（種類別）>ハッチバック>ハッチバック（新車）","自動車、乗り物>自動車>自動車（種類別）>ハッチバック>ハッチバック（中古車）","自動車、乗り物>自動車>自動車（種類別）>バン、ミニバン","自動車、乗り物>自動車>自動車（種類別）>バン、ミニバン>バン、ミニバン（新車）","自動車、乗り物>自動車>自動車（種類別）>バン、ミニバン>バン、ミニバン（中古車）","自動車、乗り物>自動車>自動車（種類別）>ピックアップ トラック","自動車、乗り物>自動車>自動車（種類別）>ピックアップ トラック>ピックアップ トラック（新車）","自動車、乗り物>自動車>自動車（種類別）>ピックアップ トラック>ピックアップ トラック（中古車）","自動車、乗り物>自動車>自動車（種類別）>マイクロカー、サブコンパクトカー","自動車、乗り物>自動車>自動車（種類別）>マイクロカー、サブコンパクトカー>マイクロカー、サブコンパクトカー（新車）","自動車、乗り物>自動車>自動車（種類別）>マイクロカー、サブコンパクトカー>マイクロカー、サブコンパクトカー（中古車）","自動車、乗り物>自動車>自動車（種類別）>ラグジュアリー カー","自動車、乗り物>自動車>自動車（種類別）>ラグジュアリー カー>高級車（新車）","自動車、乗り物>自動車>自動車（種類別）>ラグジュアリー カー>高級車（中古車）","自動車、乗り物>自動車>自動車（新車）","自動車、乗り物>自動車>自動車（中古車）","自動車、乗り物>自動車修理、メンテナンス","自動車、乗り物>自動車修理、メンテナンス>オイル交換","自動車、乗り物>自動車修理、メンテナンス>ガラス修理、交換","自動車、乗り物>自動車修理、メンテナンス>ブレーキ修理","自動車、乗り物>自動車修理、メンテナンス>自動車車体修理","自動車、乗り物>自動車修理、メンテナンス>変速機の修理","自動車、乗り物>自動車部品、アクセサリー","自動車、乗り物>自動車部品、アクセサリー>エンジン、トランスミッション","自動車、乗り物>自動車部品、アクセサリー>カーバッテリー","自動車、乗り物>自動車部品、アクセサリー>カーブレーキ","自動車、乗り物>自動車部品、アクセサリー>ホイール、タイヤ","自動車、乗り物>自動車部品、アクセサリー>高級車部品、アフターマーケット自動車部品","自動車、乗り物>自動車部品、アクセサリー>自動車外装部品、アクセサリー","自動車、乗り物>自動車部品、アクセサリー>自動車内装部品、アクセサリー","自動車、乗り物>自動車部品、アクセサリー>自動車用電子部品","自動車、乗り物>車両（その他）","自動車、乗り物>車両（その他）>キャンピングカー、RV","自動車、乗り物>車両（その他）>ボート、水上バイク","自動車、乗り物>車両（その他）>自転車、パーツ","自動車、乗り物>車両（その他）>商用自動車",
  "就業状況","就業状況>IT、技術系の求人情報","就業状況>インターンシップ","就業状況>レジャー産業、接客業","就業状況>医療系の求人情報","就業状況>営業、マーケティング系の求人情報","就業状況>会計、財務系の求人情報","就業状況>教育系の求人情報","就業状況>経営、マネジメント系の求人情報","就業状況>建設系の求人情報","就業状況>行政機関の求人情報","就業状況>事務系の求人情報","就業状況>就職相談サービス","就業状況>製造業系の求人情報","就業状況>短期アルバイト、季節的な求人情報","就業状況>農業系の求人情報","就業状況>販売系の求人情報","就業状況>法律系の求人情報","就業状況>輸送、電力系の求人情報","就業状況>履歴書、職歴",
  "食べ物","食べ物>キャンディ、チョコレート","食べ物>デリバリー、テイクアウト","食べ物>パン、焼菓子","食べ物>ピザ","食べ物>ファストフード","食べ物>食料品の宅配","食べ物>調味料、ソース","食べ物>乳製品、卵","食べ物>料理、焼き菓子の材料",
  "通信","通信>インターネット サービス プロバイダ","通信>ケーブル、衛星放送事業者","通信>携帯電話会社",
  "美容、パーソナルケア","美容、パーソナルケア>スキンケア商品","美容、パーソナルケア>スキンケア商品>化粧水、保湿剤","美容、パーソナルケア>スキンケア商品>洗顔料、メイク落とし","美容、パーソナルケア>スパ、美容サービス","美容、パーソナルケア>スパ、美容サービス>マニキュア、ペディキュア","美容、パーソナルケア>バス、ボディ商品","美容、パーソナルケア>バス、ボディ商品>ボディ ローション、保湿剤","美容、パーソナルケア>ヘアケア商品","美容、パーソナルケア>ヘアケア商品>シャンプー、コンディショナー","美容、パーソナルケア>ヘアケア商品>ヘアカラー商品","美容、パーソナルケア>メイク、化粧品","美容、パーソナルケア>メイク、化粧品>アイメイク","美容、パーソナルケア>メイク、化粧品>ネイルケア商品","美容、パーソナルケア>メイク、化粧品>フェイスメイク","美容、パーソナルケア>メイク、化粧品>リップメイク","美容、パーソナルケア>香水、フレグランス","美容、パーソナルケア>日焼け対策商品",
  "不動産","不動産>引越し、移転","不動産>居住用不動産","不動産>居住用不動産>居住用不動産（賃貸）","不動産>居住用不動産>居住用不動産（賃貸）>マンション（賃貸）","不動産>居住用不動産>居住用不動産（賃貸）>マンション（賃貸）>家具なし集合住宅","不動産>居住用不動産>居住用不動産（賃貸）>マンション（賃貸）>家具付き集合住宅","不動産>居住用不動産>居住用不動産（賃貸）>戸建（賃貸）","不動産>居住用不動産>居住用不動産（販売）","不動産>居住用不動産>居住用不動産（販売）>戸建（販売）","不動産>居住用不動産>居住用不動産（販売）>戸建（販売）>新築戸建（販売）","不動産>居住用不動産>居住用不動産（販売）>戸建（販売）>中古戸建（販売）","不動産>居住用不動産>居住用不動産（販売）>分譲マンション（販売）","不動産>居住用不動産>居住用不動産（販売）>分譲マンション（販売）>新築分譲マンション（販売）","不動産>居住用不動産>居住用不動産（販売）>分譲マンション（販売）>中古分譲マンション（販売）","不動産>商業用不動産","不動産>商業用不動産>商業用不動産（賃貸）","不動産>商業用不動産>商業用不動産（販売）",
  "幼児、子供向け製品","幼児、子供向け製品>おむつ、ベビー用衛生用品","幼児、子供向け製品>おもちゃ","幼児、子供向け製品>チャイルド シート","幼児、子供向け製品>ベビーカー","幼児、子供向け製品>育児、教育","幼児、子供向け製品>育児、教育>育児","幼児、子供向け製品>育児、教育>幼児教育","幼児、子供向け製品>乳幼児用食品","幼児、子供向け製品>乳幼児用食品>授乳用品、離乳食用品","幼児、子供向け製品>乳幼児用食品>離乳食","幼児、子供向け製品>幼児、子供服","幼児、子供向け製品>幼児、子供服>子供服","幼児、子供向け製品>幼児、子供服>幼児服",
  "旅行","旅行>バスや鉄道での旅行","旅行>ホテル、宿泊施設","旅行>ホテル、宿泊施設>ホテル（星評価別）","旅行>ホテル、宿泊施設>ホテル（星評価別）>1 つ星、2 つ星ホテル","旅行>ホテル、宿泊施設>ホテル（星評価別）>3 つ星ホテル","旅行>ホテル、宿泊施設>ホテル（星評価別）>4 つ星ホテル","旅行>ホテル、宿泊施設>ホテル（星評価別）>5 つ星ホテル","旅行>ホテル、宿泊施設>民泊","旅行>レンタカー","旅行>レンタカー>レンタカー（エコノミー、コンパクト、ミッドサイズ）","旅行>レンタカー>レンタカー（フルサイズ、スタンダード）","旅行>レンタカー>レンタカー（ミニバン、SUV）","旅行>レンタカー>レンタカー（高級、コンバーチブル、スペシャルティ）","旅行>空の旅","旅行>空の旅>飛行機旅行（クラス別）","旅行>空の旅>飛行機旅行（クラス別）>エコノミークラス","旅行>空の旅>飛行機旅行（クラス別）>ビジネスクラス、ファーストクラス","旅行>船の旅","旅行>旅（目的地別）","旅行>旅（目的地別）>アジア太平洋地域への旅","旅行>旅（目的地別）>アジア太平洋地域への旅>インドネシアへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>インドネシアへの旅>ジャカルタへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>インドネシアへの旅>バリへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>インドネシアへの旅>バンドンへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>インドへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>インドへの旅>アフマダーバードへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>インドへの旅>ゴアへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>インドへの旅>コーチ（インド）への旅","旅行>旅（目的地別）>アジア太平洋地域への旅>インドへの旅>コルカタへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>インドへの旅>ジャイプルへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>インドへの旅>チェンナイへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>インドへの旅>デリーへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>インドへの旅>ハイデラバードへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>インドへの旅>バンガロールへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>インドへの旅>プネーへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>インドへの旅>ムンバイへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>オーストラリアへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>オーストラリアへの旅>シドニーへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>オーストラリアへの旅>パースへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>オーストラリアへの旅>ブリスベーンへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>オーストラリアへの旅>メルボルンへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>シンガポールへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>スリランカへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>ソウルへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>タイへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>タイへの旅>チエンマイへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>タイへの旅>バンコクへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>タイへの旅>プーケットへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>ニュージーランドへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>ネパールへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>フィリピンへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>ベトナムへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>ベトナムへの旅>ハノイへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>ベトナムへの旅>ホーチミン市への旅","旅行>旅（目的地別）>アジア太平洋地域への旅>マレーシアへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>マレーシアへの旅>クアラルンプールへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>マレーシアへの旅>ペナンへの旅","旅行>旅（目的地別）>アジア太平洋地域への旅>香港への旅","旅行>旅（目的地別）>アジア太平洋地域への旅>台北への旅","旅行>旅（目的地別）>アジア太平洋地域への旅>中国への旅","旅行>旅（目的地別）>アジア太平洋地域への旅>中国への旅>広州への旅","旅行>旅（目的地別）>アジア太平洋地域への旅>中国への旅>上海への旅","旅行>旅（目的地別）>アジア太平洋地域への旅>中国への旅>成都への旅","旅行>旅（目的地別）>アジア太平洋地域への旅>中国への旅>北京への旅","旅行>旅（目的地別）>アジア太平洋地域への旅>日本への旅","旅行>旅（目的地別）>アジア太平洋地域への旅>日本への旅>沖縄への旅","旅行>旅（目的地別）>アジア太平洋地域への旅>日本への旅>京都、大阪、神戸への旅","旅行>旅（目的地別）>アジア太平洋地域への旅>日本への旅>札幌への旅","旅行>旅（目的地別）>アジア太平洋地域への旅>日本への旅>東京への旅","旅行>旅（目的地別）>アジア太平洋地域への旅>日本への旅>福岡への旅","旅行>旅（目的地別）>アジア太平洋地域への旅>日本への旅>名古屋への旅","旅行>旅（目的地別）>ヨーロッパへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>アイスランドへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>アイルランドへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>アイルランドへの旅>ダブリンへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>アムステルダムへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>イギリスへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>イギリスへの旅>イギリス、バーミンガムへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>イギリスへの旅>イギリス、マンチェスターへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>イギリスへの旅>イギリス、ロンドンへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>イギリスへの旅>エディンバラへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>イギリスへの旅>グラスゴーへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>イタリアへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>イタリアへの旅>シチリアへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>イタリアへの旅>ナポリへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>イタリアへの旅>フィレンツェへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>イタリアへの旅>ベネチアへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>イタリアへの旅>ボローニャへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>イタリアへの旅>ミラノへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>イタリアへの旅>ローマへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>ウィーンへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>オスロへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>キエフへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>ギリシャへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>クロアチアへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>サンクト ペテルブルグへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>スイスへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>スイスへの旅>ジュネーブへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>スイスへの旅>チューリッヒへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>スウェーデンへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>スウェーデンへの旅>ストックホルムへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>スペインへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>スペインへの旅>アリカンテへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>スペインへの旅>カナリア諸島への旅","旅行>旅（目的地別）>ヨーロッパへの旅>スペインへの旅>グラナダへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>スペインへの旅>コルドバへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>スペインへの旅>セビリアへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>スペインへの旅>バルセロナへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>スペインへの旅>バレンシアへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>スペインへの旅>ビルバオへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>スペインへの旅>マドリッドへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>スペインへの旅>マヨルカ、イビザ、バレアレス諸島への旅","旅行>旅（目的地別）>ヨーロッパへの旅>スペインへの旅>マラガへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>ソフィアへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>デンマークへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>デンマークへの旅>コペンハーゲンへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>ドイツへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>ドイツへの旅>ケルンへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>ドイツへの旅>シュトゥットガルトへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>ドイツへの旅>デュッセルドルフへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>ドイツへの旅>ハンブルクへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>ドイツへの旅>フランクフルトへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>ドイツへの旅>ベルリンへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>ドイツへの旅>ミュンヘンへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>トルコへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>トルコへの旅>アンカラへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>トルコへの旅>アンタルヤへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>トルコへの旅>イスタンブールへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>トルコへの旅>イズミルへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>ハンガリーへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>ハンガリーへの旅>ブダペストへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>プラハへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>フランスへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>フランスへの旅>トゥールーズへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>フランスへの旅>ニースへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>フランスへの旅>パリへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>フランスへの旅>ボルドーへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>フランスへの旅>マルセイユへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>フランスへの旅>リヨンへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>ブリュッセルへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>ヘルシンキへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>ポーランドへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>ポーランドへの旅>クラクフへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>ポーランドへの旅>ワルシャワへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>ポルトガルへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>ポルトガルへの旅>ファロへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>ポルトガルへの旅>ポルトへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>ポルトガルへの旅>リスボンへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>マルタへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>モスクワへの旅","旅行>旅（目的地別）>ヨーロッパへの旅>ルーマニアへの旅","旅行>旅（目的地別）>ラテンアメリカへの旅","旅行>旅（目的地別）>ラテンアメリカへの旅>カリブ諸国への旅","旅行>旅（目的地別）>ラテンアメリカへの旅>カリブ諸国への旅>アルバへの旅","旅行>旅（目的地別）>ラテンアメリカへの旅>カリブ諸国への旅>キューバへの旅","旅行>旅（目的地別）>ラテンアメリカへの旅>カリブ諸国への旅>ジャマイカへの旅","旅行>旅（目的地別）>ラテンアメリカへの旅>カリブ諸国への旅>ドミニカ共和国への旅","旅行>旅（目的地別）>ラテンアメリカへの旅>カリブ諸国への旅>ナッソーへの旅","旅行>旅（目的地別）>ラテンアメリカへの旅>カリブ諸国への旅>プエルトリコへの旅","旅行>旅（目的地別）>ラテンアメリカへの旅>グアテマラへの旅","旅行>旅（目的地別）>ラテンアメリカへの旅>コスタリカへの旅","旅行>旅（目的地別）>ラテンアメリカへの旅>サンチアゴへの旅","旅行>旅（目的地別）>ラテンアメリカへの旅>パナマへの旅","旅行>旅（目的地別）>ラテンアメリカへの旅>ブエノスアイレスへの旅","旅行>旅（目的地別）>ラテンアメリカへの旅>ブラジルへの旅","旅行>旅（目的地別）>ラテンアメリカへの旅>ブラジルへの旅>サルバドルへの旅","旅行>旅（目的地別）>ラテンアメリカへの旅>ブラジルへの旅>サンパウロへの旅","旅行>旅（目的地別）>ラテンアメリカへの旅>ブラジルへの旅>リオデジャネイロへの旅","旅行>旅（目的地別）>ラテンアメリカへの旅>ボゴタへの旅","旅行>旅（目的地別）>ラテンアメリカへの旅>メキシコへの旅","旅行>旅（目的地別）>ラテンアメリカへの旅>メキシコへの旅>アカプルコへの旅","旅行>旅（目的地別）>ラテンアメリカへの旅>メキシコへの旅>カンクン、プラヤ デル カルメンへの旅","旅行>旅（目的地別）>ラテンアメリカへの旅>メキシコへの旅>グアダラハラへの旅","旅行>旅（目的地別）>ラテンアメリカへの旅>メキシコへの旅>プエルト バヤルタへの旅","旅行>旅（目的地別）>ラテンアメリカへの旅>メキシコへの旅>メキシコシティへの旅","旅行>旅（目的地別）>ラテンアメリカへの旅>メキシコへの旅>モンテレイへの旅","旅行>旅（目的地別）>ラテンアメリカへの旅>メキシコへの旅>ロスカボスへの旅","旅行>旅（目的地別）>ラテンアメリカへの旅>リマへの旅","旅行>旅（目的地別）>中東、アフリカへの旅","旅行>旅（目的地別）>中東、アフリカへの旅>アラブ首長国連邦への旅","旅行>旅（目的地別）>中東、アフリカへの旅>アラブ首長国連邦への旅>アブダビへの旅","旅行>旅（目的地別）>中東、アフリカへの旅>アラブ首長国連邦への旅>ドバイへの旅","旅行>旅（目的地別）>中東、アフリカへの旅>イスラエルへの旅","旅行>旅（目的地別）>中東、アフリカへの旅>エジプトへの旅","旅行>旅（目的地別）>中東、アフリカへの旅>クウェートへの旅","旅行>旅（目的地別）>中東、アフリカへの旅>ケニアへの旅","旅行>旅（目的地別）>中東、アフリカへの旅>ジッダへの旅","旅行>旅（目的地別）>中東、アフリカへの旅>テヘランへの旅","旅行>旅（目的地別）>中東、アフリカへの旅>ドーハへの旅","旅行>旅（目的地別）>中東、アフリカへの旅>モロッコへの旅","旅行>旅（目的地別）>中東、アフリカへの旅>ヨルダンへの旅","旅行>旅（目的地別）>中東、アフリカへの旅>リヤドへの旅","旅行>旅（目的地別）>中東、アフリカへの旅>レバノンへの旅","旅行>旅（目的地別）>中東、アフリカへの旅>南アフリカへの旅","旅行>旅（目的地別）>中東、アフリカへの旅>南アフリカへの旅>ケープタウンへの旅","旅行>旅（目的地別）>中東、アフリカへの旅>南アフリカへの旅>ヨハネスブルグへの旅","旅行>旅（目的地別）>北アメリカへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>アトランタへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>アトランティック シティへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>アラスカへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>アラバマ州バーミンガムへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>アルバカーキへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>インディアナポリスへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>オースティンへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>オクラホマシティへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>オマハへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>ガットリンバーグへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>カリフォルニアへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>カリフォルニアへの旅>カリフォルニア州オレンジ カウンティへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>カリフォルニアへの旅>サンディエゴへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>カリフォルニアへの旅>サンフランシスコ ベイエリアへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>カリフォルニアへの旅>ロサンゼルスへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>カンザスシティへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>クリーブランドへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>コロンバスへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>サウスカロライナ州グリーンビルへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>サウスカロライナ州チャールストンへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>サバナへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>サンアントニオへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>シアトルへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>シカゴへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>シャーロットへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>シンシナティへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>セントルイスへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>ソルトレイクシティへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>ダラス フォートワースへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>ツーソンへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>デトロイトへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>デンバーへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>ナッシュビルへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>ニューオーリンズへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>ニューヨーク市への旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>ノーフォーク バージニアビーチ地域への旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>バージニア州リッチモンドへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>ハートフォードへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>バッファロー ロチェスター エリアへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>ハワイへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>ハワイへの旅>オアフへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>ハワイへの旅>ビッグ アイランドへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>ハワイへの旅>マウイへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>ピッツバーグへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>ヒューストンへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>フィラデルフィアへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>フェニックスへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>フロリダへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>フロリダへの旅>オーランドへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>フロリダへの旅>キーウェストへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>フロリダへの旅>ジャクソンビルへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>フロリダへの旅>タンパ ベイエリアへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>フロリダへの旅>フォート マイヤーズへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>フロリダへの旅>フォートローダーデールへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>フロリダへの旅>ペンサコラへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>フロリダへの旅>マイアミへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>ポートランドへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>ボストンへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>ボルティモアへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>マートルビーチ、グランド ストランドへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>ミネアポリス セントポールへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>ミルウォーキーへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>メリーランド州オーシャン シティへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>メンフィスへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>ラスベガスへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>リーノーへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>リトルロックへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>ルイビルへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>ローリー ダーラム エリアへの旅","旅行>旅（目的地別）>北アメリカへの旅>アメリカへの旅>ワシントン DC への旅","旅行>旅（目的地別）>北アメリカへの旅>カナダへの旅","旅行>旅（目的地別）>北アメリカへの旅>カナダへの旅>オタワへの旅","旅行>旅（目的地別）>北アメリカへの旅>カナダへの旅>カルガリーへの旅","旅行>旅（目的地別）>北アメリカへの旅>カナダへの旅>トロントへの旅","旅行>旅（目的地別）>北アメリカへの旅>カナダへの旅>バンクーバーへの旅","旅行>旅（目的地別）>北アメリカへの旅>カナダへの旅>モントリオールへの旅"
]
const targetingListMap = {
  'アフィニティカテゴリ': affinityList,
  'インマーケットセグメント': inMarketList,
  '詳しいユーザー属性': detailedDemographicsList,
  'ライフイベント': lifeEventsList
}
const targetModalOpen = ref(false)
const targetSlot = ref(1)
const targetSearch = ref('')
const pendingTargetNames = ref([])
const pendingTargetText = ref('')
const isFreeTextTargetingType = computed(() => {
  const type = editingPattern.value[`targeting${targetSlot.value}Type`]
  return targetingFreeTextTypes.includes(type)
})
const currentTargetingList = computed(() => {
  const type = editingPattern.value[`targeting${targetSlot.value}Type`]
  return targetingListMap[type] || []
})
const targetModalTitle = computed(() => {
  const type = editingPattern.value[`targeting${targetSlot.value}Type`]
  if (isFreeTextTargetingType.value) return `${type} - 内容入力`
  return `${type}を選択`
})
const filteredTargetOptions = computed(() => {
  const q = targetSearch.value.trim().toLowerCase()
  const list = currentTargetingList.value
  return q ? list.filter(value => value.toLowerCase().includes(q)) : list
})
const selectedTargetValues = computed(() => pendingTargetNames.value)
const onTargetingTypeChange = (slot) => {
  editingPattern.value[`targeting${slot}Values`] = []
}
const openTargetModal = (slot) => {
  targetSlot.value = slot
  const type = editingPattern.value[`targeting${slot}Type`]
  const currentValues = editingPattern.value[`targeting${slot}Values`] || []
  if (targetingFreeTextTypes.includes(type)) {
    pendingTargetText.value = currentValues[0] || ''
  } else {
    pendingTargetNames.value = [...currentValues]
  }
  targetSearch.value = ''
  targetModalOpen.value = true
}
const closeTargetModal = () => {
  targetModalOpen.value = false
  targetSearch.value = ''
}
const clearTargetSelection = () => {
  pendingTargetNames.value = []
}
const toggleTarget = (value) => {
  if (pendingTargetNames.value.includes(value)) {
    removeTarget(value)
  } else {
    pendingTargetNames.value.push(value)
  }
}
const removeTarget = (name) => {
  pendingTargetNames.value = pendingTargetNames.value.filter(value => value !== name)
}
const confirmTargetSelection = () => {
  const type = editingPattern.value[`targeting${targetSlot.value}Type`]
  if (targetingFreeTextTypes.includes(type)) {
    const trimmed = pendingTargetText.value.trim()
    editingPattern.value[`targeting${targetSlot.value}Values`] = trimmed ? [trimmed] : []
  } else {
    editingPattern.value[`targeting${targetSlot.value}Values`] = [...pendingTargetNames.value]
  }
  closeTargetModal()
}
const addYoutubePattern = () => {
  const newPattern = createEmptyPattern()
  youtubePatterns.push(newPattern)
  openEditModal(newPattern, youtubePatterns.length - 1)
}
const removeYoutubePattern = (index) => {
  if (youtubePatterns.length > 1) {
    youtubePatterns.splice(index, 1)
  }
}
const copyYoutubePattern = (index) => {
  const source = youtubePatterns[index]
  const copy = JSON.parse(JSON.stringify(source))
  copy.id = `yt-${Date.now()}-${youtubePatternSeq++}`
  youtubePatterns.splice(index + 1, 0, copy)
  openEditModal(copy, index + 1)
}
const touched = reactive({
  email: false
})
const departmentOptions = [
  { value: 'tokyo', label: '東京' },
  { value: 'kansai', label: '関西' },
  { value: 'chubu', label: '中部' },
  { value: 'kyushu', label: '九州' }
]
const isFieldDimmed = () => {
  // 常時グレー掛けはしない（未入力でもフォーカス外でも常に通常表示）
  return false
}
const isEmailInvalid = computed(() => {
  if (!formData.email.trim()) return false
  const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/
  return touched.email && !emailPattern.test(formData.email.trim())
})
const isStep1Valid = computed(() => {
  return (
    formData.applicantName.trim() !== '' &&
    formData.department.trim() !== '' &&
    formData.clientName.trim() !== '' &&
    formData.projectName.trim() !== '' &&
    !isEmailInvalid.value
  )
})
const platformList = [
  {
    id: 'youtube',
    name: 'YouTube',
    logos: [{ lottiePath: '/images_json/YouTube.lottie', class: 'youtube' }]
  },
  {
    id: 'yg',
    name: 'YG-Display&DGC',
    // YG（Yahoo!・Google）はロゴアニメーションではなく、静止画のロゴ2つを横並びで表示する
    logos: [
      { imagePath: '/images_json/YG-Google.webp', class: 'yg-google' },
      { imagePath: '/images_json/YG-LineYahoo.png', class: 'yg-line-yahoo' }
    ]
  },
  {
    id: 'listing',
    name: 'Listing',
    logos: [{ lottiePath: '/images_json/Listing.lottie', class: 'listing' }]
  },
  {
    id: 'meta',
    name: 'Meta',
    logos: [{ lottiePath: '/images_json/Meta.lottie', class: 'meta' }],
    comingSoon: true
  },
  {
    id: 'line',
    name: 'LINE',
    // LINEはアニメーションではなく静止画ロゴを使用
    logos: [{ imagePath: '/images_json/LINE_logo.svg.webp', class: 'line' }],
    comingSoon: true
  },
  {
    id: 'x',
    name: 'X',
    // Xはアニメーションではなく静止画ロゴを使用
    logos: [{ imagePath: '/images_json/X_logo_2023.svg.webp', class: 'x' }],
    comingSoon: true
  },
  {
    id: 'dv360',
    name: 'DV360',
    // DV360はアニメーションではなく静止画ロゴを使用
    logos: [{ imagePath: '/images_json/logo_display_video_360_192px.svg', class: 'dv360' }],
    comingSoon: true
  }
]
const platforms = reactive(
  platformList.reduce((acc, platform) => {
    acc[platform.id] = {
      selected: false,
      marginKind: 'normal', // 'normal' | 'irregular'（全媒体共通のドロップダウン。デフォルトは通常）
      baRate: null,
      oneRate: null
    }
    return acc
  }, {})
)

// 媒体ごとのマージン設定。「通常／イレギュラー」の選択自体は全プラットフォーム共通だが、
// 媒体によって①通常時の固定マージン率と②計算方式（乗算 or 逆算）が異なる。
// G＝グロス金額、N＝ネット金額（クライアントからの入稿予算）。
// ・YG-Display&DGCの通常のみ「ネットに一律20%を上乗せ」という特別ルール（G = N × 1.2）
// ・それ以外の通常、および全媒体共通のイレギュラー（BA・ONEなどの内訳合計）は、
//   取り分をグロスに対する率として扱うため N = G × (1 − 総マージン率) → G = N ÷ (1 − 総マージン率) で逆算する
//   （例：総マージン率30%のとき、G = N × 1/(1-0.3) = N × 1.42857）
const platformMarginConfig = {
  meta:    { normalRate: 30, formula: 'divisive' },
  // 選択可能な3媒体（YouTube・YG-Display&DGC・Listing）の通常マージン率はすべて20%
  listing: { normalRate: 20, formula: 'divisive' },
  youtube: { normalRate: 20, formula: 'divisive' },
  yg:      { normalRate: 20, formula: 'additive' },
  line:    { normalRate: 30, formula: 'divisive' },
  x:       { normalRate: 30, formula: 'divisive' },
  dv360:   { normalRate: 30, formula: 'divisive' }
}
const getPlatformNormalRate = (id) => platformMarginConfig[id]?.normalRate ?? 20
const getPlatformFormulaType = (id) => platformMarginConfig[id]?.formula ?? 'divisive'
const platformTotalMarginRate = (id) => {
  const p = platforms[id]
  if (!p) return 0
  if (p.marginKind === 'normal') return getPlatformNormalRate(id)
  const ba = Number(p.baRate) || 0
  const one = Number(p.oneRate) || 0
  return ba + one
}
const platformMarginMultiplier = (id) => {
  const p = platforms[id]
  if (!p) return null
  const totalRate = platformTotalMarginRate(id)
  if (totalRate <= 0 || totalRate >= 100) return null
  if (p.marginKind === 'normal' && getPlatformFormulaType(id) === 'additive') {
    return 1 + totalRate / 100
  }
  return 1 / (1 - totalRate / 100)
}
const platformMarginFormulaText = (id) => {
  const multiplier = platformMarginMultiplier(id)
  if (multiplier === null) return 'G = N × —'
  // 小数第5位までの丸め表示（例：1.2、1.42857）
  const rounded = Math.round(multiplier * 100000) / 100000
  return `G = N × ${rounded}`
}
const setLottieRef = (el, id) => {
  if (el) {
    lottieRefs[id] = el
  }
}
const getDotLottie = (id) => {
  const lottieComp = lottieRefs[id]
  if (!lottieComp) return null
  return lottieComp.getDotLottieInstance ? lottieComp.getDotLottieInstance() : null
}
const onMouseEnter = (id) => {
  hoveredPlatform.value = id
  const dotLottie = getDotLottie(id)
  if (dotLottie) {
    dotLottie.play()
  }
}
const onMouseLeave = (id) => {
  hoveredPlatform.value = null
  if (!platforms[id].selected) {
    const dotLottie = getDotLottie(id)
    if (dotLottie) {
      dotLottie.stop()
    }
  }
}
const togglePlatform = (id) => {
  const platform = platformList.find(p => p.id === id)
  if (platform?.comingSoon) return
  platforms[id].selected = !platforms[id].selected
  const dotLottie = getDotLottie(id)
  if (!dotLottie) return
  if (platforms[id].selected) {
    dotLottie.play()
  } else if (hoveredPlatform.value !== id) {
    dotLottie.stop()
  }
}
const isStep2Valid = computed(() => {
  return Object.values(platforms).some(platform => platform.selected)
})
const goToFormStep = () => {
  currentStep.value = 3
  // 選択されていない媒体のタブが残ったままにならないよう、選択済みの先頭の媒体に合わせる
  const firstSelected = selectedPlatformObjects.value[0]
  if (firstSelected) {
    activePlatformTab.value = firstSelected.name
  }
  // 遷移直後にパターン詳細編集モーダルを自動で開かない（一覧画面のまま表示する）
}
/* STEP 3用 タブ制御機能 */
const activePlatformTab = ref('YouTube')
const selectedPlatforms = computed(() => {
  return platformList
    .filter(p => platforms[p.id]?.selected)
    .map(p => p.name)
})
const selectedPlatformObjects = computed(() => {
  return platformList.filter(p => platforms[p.id]?.selected)
})
// platformList側の name はすでに表示用の見た目（Meta／Listing／YouTube／YG-Display&DGC など）で
// 統一済みなので、そのままタブラベルとして使う（大文字化・小文字化の変換は不要）
const capitalizeLabel = (name) => name || ''
/* --- TABLE FORMタブ：選択中のタブに追従してスライドする下線インジケーター --- */
const tabButtonRefs = reactive({})
const setTabButtonRef = (el, id) => {
  if (el) {
    tabButtonRefs[id] = el
  }
}
const tabIndicatorStyle = ref({ left: '0px', width: '0px', opacity: 0 })
const updateTabIndicator = () => {
  const activePlatform = selectedPlatformObjects.value.find(p => p.name === activePlatformTab.value)
  if (!activePlatform) {
    tabIndicatorStyle.value = { left: '0px', width: '0px', opacity: 0 }
    return
  }
  const el = tabButtonRefs[activePlatform.id]
  if (!el) return
  tabIndicatorStyle.value = {
    left: el.offsetLeft + 'px',
    width: el.offsetWidth + 'px',
    opacity: 1
  }
}
watch(activePlatformTab, () => {
  nextTick(() => updateTabIndicator())
})
watch(currentStep, (newStep) => {
  if (newStep === 3 && selectedPlatforms.value.length > 0) {
    if (!selectedPlatforms.value.includes(activePlatformTab.value)) {
      activePlatformTab.value = selectedPlatforms.value[0]
    }
    nextTick(() => updateTabIndicator())
  }
})
/* =========================================================
   META: パターン設定データ（GASコードを再現）
========================================================= */
let metaPatternSeq = 1
const createEmptyMetaPattern = () => ({
  id: `meta-${Date.now()}-${metaPatternSeq++}`,
  campaignObjective: '',
  kpi: '',
  menu: '',
  placement: [],
  billing: 'CPM課金',
  periodNumber: 1,
  periodUnit: 'ヶ月間',
  periodTiming: '',
  budget: null,
  age1: 16,
  age2: '',
  gender: '男女',
  device: 'ALL（SD＋PC）',
  prefId: 'national',
  prefName: '全国',
  prefNames: ['全国'],
  city: '',
  fqType: '推奨',
  fqNum: '',
  fqDays: '',
  interest: '',
  remarks: ''
})
const metaPatterns = reactive([createEmptyMetaPattern()])
const isMetaEditModalOpen = ref(false)
const metaEditingIndex = ref(null)
const editingMetaPattern = ref({})
const metaCampaignObjectives = ['リーチ', 'クリック', '動画再生 15秒', '動画再生 30秒', '動画再生 60秒', 'コンバージョン', 'エンゲージメント']
const metaKpiMap = {
  'リーチ': { fixed: 'CPM' },
  'クリック': { fixed: 'CPC' },
  '動画再生 15秒': { choices: ['CPV', 'CPCV'] },
  '動画再生 30秒': { choices: ['CPV', 'CPCV'] },
  '動画再生 60秒': { choices: ['CPV', 'CPCV'] },
  'コンバージョン': { fixed: 'CPA' },
  'エンゲージメント': { fixed: 'CPE' }
}
const metaMenuMap = {
  'リーチ': ['Link Ad', 'Link Ad カルーセル', 'Link Video Ad'],
  'クリック': ['Link Ad', 'Link Ad カルーセル', 'Link Video Ad'],
  '動画再生 15秒': ['Link Video Ad'],
  '動画再生 30秒': ['Link Video Ad'],
  '動画再生 60秒': ['Link Video Ad'],
  'コンバージョン': ['Link Ad', 'Link Ad カルーセル', 'Link Video Ad'],
  'エンゲージメント': ['Link Ad', 'Link Video Ad']
}
const metaPlacementOptions = ['★ALL', 'IGフィード', 'IGストーリーズ/リール', 'FBフィード', 'FBストーリーズ/リール']
const getMetaKpiChoices = (objective) => {
  const def = metaKpiMap[objective]
  if (!def) return []
  return def.fixed ? [def.fixed] : def.choices
}
const isMetaKpiFixed = (objective) => {
  const def = metaKpiMap[objective]
  return !!(def && def.fixed)
}
const getMetaMenuOptions = (objective) => metaMenuMap[objective] || []
const onMetaObjectiveChange = () => {
  const objective = editingMetaPattern.value.campaignObjective
  const kpiDef = metaKpiMap[objective]
  editingMetaPattern.value.kpi = kpiDef && kpiDef.fixed ? kpiDef.fixed : ''
  const menus = metaMenuMap[objective] || []
  editingMetaPattern.value.menu = menus.length === 1 ? menus[0] : ''
  editingMetaPattern.value.placement = []
}
const onMetaMenuChange = () => {
  editingMetaPattern.value.placement = []
}
const toggleMetaPlacement = (value) => {
  if (value === '★ALL') {
    editingMetaPattern.value.placement = ['★ALL']
    return
  }
  const next = editingMetaPattern.value.placement.filter(v => v !== '★ALL')
  const idx = next.indexOf(value)
  if (idx >= 0) {
    next.splice(idx, 1)
  } else {
    next.push(value)
  }
  editingMetaPattern.value.placement = next
}
const isMetaUnder18 = computed(() => Number(editingMetaPattern.value.age1) <= 17)
const isMetaAge2Disabled = computed(() => Number(editingMetaPattern.value.age1) >= 65)
const isMetaCityDisabled = computed(() => {
  return isMetaUnder18.value || (editingMetaPattern.value.prefNames && editingMetaPattern.value.prefNames.includes('全国'))
})
watch(() => editingMetaPattern.value.age1, (val) => {
  const age1 = Number(val)
  if (!age1) return
  if (age1 <= 17) {
    editingMetaPattern.value.gender = '男女'
    editingMetaPattern.value.city = ''
    editingMetaPattern.value.interest = ''
  }
  if (age1 >= 65) {
    editingMetaPattern.value.age2 = ''
  }
})
const onMetaFqTypeChange = () => {
  if (editingMetaPattern.value.fqType !== '指定') {
    editingMetaPattern.value.fqNum = ''
    editingMetaPattern.value.fqDays = ''
  }
}
const editingMetaBudgetDisplay = computed({
  get: () => {
    const val = editingMetaPattern.value.budget
    return val ? `¥${Number(val).toLocaleString()}` : ''
  },
  set: (val) => {
    const digits = String(val).replace(/[^\d]/g, '')
    editingMetaPattern.value.budget = digits ? Number(digits) : null
  }
})
/* --- Meta 概要テーブル表示用フォーマット関数 --- */
const formatMetaPeriod = (pattern) => {
  const base = pattern.periodNumber ? `${pattern.periodNumber}${pattern.periodUnit}` : '未設定'
  return pattern.periodTiming ? `${base}（${pattern.periodTiming}）` : base
}
const formatMetaBudget = (pattern) => {
  return pattern.budget ? `¥${Number(pattern.budget).toLocaleString()}` : '未設定'
}
const formatMetaAge = (pattern) => {
  if (!pattern.age1) return '未設定'
  return pattern.age2 ? `${pattern.age1}〜${pattern.age2}歳` : `${pattern.age1}歳〜`
}
const formatMetaArea = (pattern) => {
  if (!pattern.prefNames || !pattern.prefNames.length) return '未設定'
  const base = pattern.prefNames.join(' / ')
  return pattern.city ? `${base}・${pattern.city}` : base
}
const formatMetaFq = (pattern) => {
  if (pattern.fqType !== '指定') return '推奨'
  if (pattern.fqNum && pattern.fqDays) return `${pattern.fqNum}回 / ${pattern.fqDays}日間`
  return '指定'
}
/* --- Meta 編集モーダルの状態管理 --- */
const openMetaEditModal = (pattern, index) => {
  metaEditingIndex.value = index
  editingMetaPattern.value = JSON.parse(JSON.stringify(pattern))
  activeFieldPopover.value = null
  isMetaEditModalOpen.value = true
}
const closeMetaEditModal = () => {
  isMetaEditModalOpen.value = false
  metaEditingIndex.value = null
  editingMetaPattern.value = {}
  activeFieldPopover.value = null
}
const saveMetaEditModal = () => {
  if (metaEditingIndex.value !== null && metaEditingIndex.value >= 0 && metaEditingIndex.value < metaPatterns.length) {
    metaPatterns.splice(metaEditingIndex.value, 1, JSON.parse(JSON.stringify(editingMetaPattern.value)))
  }
  closeMetaEditModal()
}
const addMetaPattern = () => {
  const newPattern = createEmptyMetaPattern()
  metaPatterns.push(newPattern)
  openMetaEditModal(newPattern, metaPatterns.length - 1)
}
const removeMetaPattern = (index) => {
  if (metaPatterns.length > 1) {
    metaPatterns.splice(index, 1)
  }
}
const copyMetaPattern = (index) => {
  const source = metaPatterns[index]
  const copy = JSON.parse(JSON.stringify(source))
  copy.id = `meta-${Date.now()}-${metaPatternSeq++}`
  metaPatterns.splice(index + 1, 0, copy)
  openMetaEditModal(copy, index + 1)
}
/* =========================================================
   YG-Display&DGC: パターン設定データ（GASコードを再現）
========================================================= */
let ygPatternSeq = 1
const createEmptyYgPattern = () => ({
  id: `yg-${Date.now()}-${ygPatternSeq++}`,
  menu: '',
  videoDuration: '',
  billing: '',
  periodNumber: 1,
  periodUnit: 'ヶ月間',
  budget: null,
  prefId: 'national',
  prefName: '全国',
  prefNames: ['全国'],
  city: '',
  gender: 'ALL',
  age1: 'ALL',
  age2: '',
  age3: '',
  targeting1Type: 'なし',
  targeting1Values: [],
  targeting2Type: 'なし',
  targeting2Values: [],
  targeting3Type: 'なし',
  targeting3Values: [],
  device: ['ALL'],
  remarks: ''
})
const ygPatterns = reactive([createEmptyYgPattern()])
const ygMenuOptions = [
  'GDA',
  'YDA',
  'YDA RES動画（動画再生）',
  'YDA PCブラパネ動画（ブランド認知）',
  'YDA SPブラパネ動画（ブランド認知）',
  'DGC（画像）',
  'DGC（動画）',
  'DGC（画像＋動画）'
]
const ygIsYda = (menu) => {
  return menu === 'YDA' || menu === 'YDA RES動画（動画再生）' || menu === 'YDA PCブラパネ動画（ブランド認知）' || menu === 'YDA SPブラパネ動画（ブランド認知）'
}
const ygIsVideoMenu = (menu) => {
  return menu === 'YDA RES動画（動画再生）' || menu === 'YDA PCブラパネ動画（ブランド認知）' || menu === 'YDA SPブラパネ動画（ブランド認知）' || menu === 'DGC（動画）' || menu === 'DGC（画像＋動画）'
}
const ygIsDgcVideo = (menu) => {
  return menu === 'DGC（動画）' || menu === 'DGC（画像＋動画）'
}
const ygBillingOptions = (menu) => {
  if (menu === 'GDA' || menu === 'YDA') return ['CPC課金', 'vCPM課金']
  if (menu === 'YDA RES動画（動画再生）') return ['CPV課金', 'vCPM（β版）']
  if (menu === 'YDA PCブラパネ動画（ブランド認知）' || menu === 'YDA SPブラパネ動画（ブランド認知）') return ['vCPM課金']
  if (menu && menu.startsWith('DGC')) return ['MIX課金']
  return []
}
const ygIsBillingFixed = (menu) => {
  return menu === 'YDA PCブラパネ動画（ブランド認知）' || menu === 'YDA SPブラパネ動画（ブランド認知）' || (menu && menu.startsWith('DGC'))
}
const ygAge1Options = (menu) => {
  return ygIsYda(menu)
    ? ['ALL', '18~', '20~', '25~', '30~', '35~', '40~', '45~', '50~', '55~', '60~', '65~', '70~']
    : ['ALL', '18~', '25~', '35~', '45~', '55~', '65歳以上']
}
const ygAge2Options = (menu, age1) => {
  const all = ygIsYda(menu)
    ? ['19歳', '24歳', '29歳', '34歳', '39歳', '44歳', '49歳', '54歳', '59歳', '64歳', '69歳', '以上すべて']
    : ['24歳', '34歳', '44歳', '54歳', '64歳', '以上すべて']
  if (!age1 || age1 === 'ALL') return all
  const startAge = parseInt(String(age1).replace(/[^0-9]/g, ''), 10)
  return all.filter(v => v === '以上すべて' || parseInt(v.replace(/[^0-9]/g, ''), 10) >= startAge)
}
const ygDeviceOptions = (menu) => {
  if (menu === 'YDA PCブラパネ動画（ブランド認知）') return ['PC', 'TB']
  if (menu === 'YDA SPブラパネ動画（ブランド認知）') return ['SP']
  const base = ['ALL', 'PC', 'TB', 'SP']
  if (ygIsDgcVideo(menu)) base.push('CTV')
  return base
}
const ygIsDeviceFixed = (menu) => {
  return menu === 'YDA PCブラパネ動画（ブランド認知）' || menu === 'YDA SPブラパネ動画（ブランド認知）'
}
const ygTargetingTypeOptions = (menu) => {
  if (ygIsYda(menu)) {
    return ['なし', '興味関心', '購買意向', '属性・ライフイベント', '高度なセグメント', 'コンテンツキーワード', 'プレースメント']
  }
  return ['なし', 'アフィニティカテゴリ', 'インマーケットセグメント', '詳しいユーザー属性', 'ライフイベント', 'カスタムオーディエンス', 'トピック', 'コンテンツ', 'プレースメント']
}
const ygTargetingFreeTextTypes = ['カスタムオーディエンス', 'コンテンツキーワード', '高度なセグメント', 'トピック', 'コンテンツ', 'プレースメント']
const ygTargetingListMap = {
  'アフィニティカテゴリ': affinityList,
  'インマーケットセグメント': inMarketList,
  '詳しいユーザー属性': detailedDemographicsList,
  'ライフイベント': lifeEventsList
}
const ydaInterestList = [
  "インテリア、DIY","インテリア、DIY/DIY 愛好者","インテリア、DIY/インテリア好き",
  "グルメ、料理","グルメ、料理/お菓子好き","グルメ、料理/お酒好き","グルメ、料理/コーヒー好き","グルメ、料理/菜食主義","グルメ、料理/出前、宅配好き","グルメ、料理/食通","グルメ、料理/料理好き",
  "ゲーム","ゲーム/ゲーム好き","ゲーム/ゲーム好き/アクションゲーム","ゲーム/ゲーム好き/アドベンチャーゲーム","ゲーム/ゲーム好き/オンラインゲーム","ゲーム/ゲーム好き/カジノゲーム","ゲーム/ゲーム好き/シミュレーションゲーム","ゲーム/ゲーム好き/シューティングゲーム","ゲーム/ゲーム好き/ストラテジーゲーム","ゲーム/ゲーム好き/スポーツゲーム","ゲーム/ゲーム好き/バラエティーゲーム","ゲーム/ゲーム好き/レースゲーム","ゲーム/ゲーム好き/ロールプレーイングゲーム","ゲーム/ゲーム好き/格闘ゲーム","ゲーム/ゲーム好き/恋愛ゲーム","ゲーム/ゲーム好き/パズルゲーム",
  "ショッピング","ショッピング/コンビニ・スーパー・ドラッグストア好き","ショッピング/ショッピングモール・アウトレット好き","ショッピング/デパート好き","ショッピング/高級ブランド好き","ショッピング/買い物好き",
  "スポーツ、フィットネス","スポーツ、フィットネス/スポーツ好き","スポーツ、フィットネス/スポーツ好き/F1","スポーツ、フィットネス/スポーツ好き/ゴルフ","スポーツ、フィットネス/スポーツ好き/サイクリング","スポーツ、フィットネス/スポーツ好き/サッカー","スポーツ、フィットネス/スポーツ好き/サッカー/Jリーグ","スポーツ、フィットネス/スポーツ好き/サッカー/海外サッカー","スポーツ、フィットネス/スポーツ好き/スキー","スポーツ、フィットネス/スポーツ好き/スノーボード","スポーツ、フィットネス/スポーツ好き/ダンス、バレエ","スポーツ、フィットネス/スポーツ好き/テニス","スポーツ、フィットネス/スポーツ好き/バスケットボール","スポーツ、フィットネス/スポーツ好き/バドミントン","スポーツ、フィットネス/スポーツ好き/バレーボール","スポーツ、フィットネス/スポーツ好き/フィギュアスケート","スポーツ、フィットネス/スポーツ好き/マリンスポーツ","スポーツ、フィットネス/スポーツ好き/ランニング","スポーツ、フィットネス/スポーツ好き/格闘技","スポーツ、フィットネス/スポーツ好き/競馬","スポーツ、フィットネス/スポーツ好き/水泳","スポーツ、フィットネス/スポーツ好き/相撲","スポーツ、フィットネス/スポーツ好き/卓球","スポーツ、フィットネス/スポーツ好き/野球","スポーツ、フィットネス/スポーツ好き/野球/プロ野球","スポーツ、フィットネス/スポーツ好き/野球/メジャーリーグ","スポーツ、フィットネス/スポーツ好き/野球/高校野球","スポーツ、フィットネス/フィットネス、トレーニング好き","スポーツ、フィットネス/ヨガ、ピラティス好き",
  "デジタル機器、家電","デジタル機器、家電/PC好き","デジタル機器、家電/オーディオ好き","デジタル機器、家電/ガジェット好き","デジタル機器、家電/スマホ・タブレット好き","デジタル機器、家電/家電好き",
  "ニュース、情報メディア","ニュース、情報メディア/ニュース好き","ニュース、情報メディア/ニュース好き/エンタメ","ニュース、情報メディア/ニュース好き/サイエンス（科学）","ニュース、情報メディア/ニュース好き/テクノロジー（IT）","ニュース、情報メディア/ニュース好き/経済","ニュース、情報メディア/ニュース好き/政治","ニュース、情報メディア/女性向けメディア好き","ニュース、情報メディア/男性向けメディア好き",
  "メディア、エンターテインメント","メディア、エンターテインメント/アイドル好き","メディア、エンターテインメント/アイドル好き/女性アイドル","メディア、エンターテインメント/アイドル好き/男性アイドル","メディア、エンターテインメント/インフルエンサー好き","メディア、エンターテインメント/タレント好き","メディア、エンターテインメント/テレビ好き","メディア、エンターテインメント/テレビ好き/アニメ","メディア、エンターテインメント/テレビ好き/スポーツ","メディア、エンターテインメント/テレビ好き/ドキュメンタリー、教養","メディア、エンターテインメント/テレビ好き/ニュース、報道","メディア、エンターテインメント/テレビ好き/バラエティー","メディア、エンターテインメント/テレビ好き/音楽","メディア、エンターテインメント/テレビ好き/海外ドラマ","メディア、エンターテインメント/テレビ好き/国内ドラマ","メディア、エンターテインメント/テレビ好き/情報、ワイドショー","メディア、エンターテインメント/マンガ好き","メディア、エンターテインメント/ラジオ好き","メディア、エンターテインメント/映画好き","メディア、エンターテインメント/映画好き/SF映画、ファンタジー映画","メディア、エンターテインメント/映画好き/アクション映画、アドベンチャー映画","メディア、エンターテインメント/映画好き/アニメ映画","メディア、エンターテインメント/映画好き/コメディー映画","メディア、エンターテインメント/映画好き/サスペンス映画","メディア、エンターテインメント/映画好き/ドキュメンタリー映画","メディア、エンターテインメント/映画好き/ドラマ映画","メディア、エンターテインメント/映画好き/ファミリー映画","メディア、エンターテインメント/映画好き/ホラー映画","メディア、エンターテインメント/映画好き/韓国映画","メディア、エンターテインメント/映画好き/邦画","メディア、エンターテインメント/映画好き/洋画","メディア、エンターテインメント/映画好き/恋愛映画","メディア、エンターテインメント/演劇好き","メディア、エンターテインメント/音楽好き","メディア、エンターテインメント/音楽好き/K-POP","メディア、エンターテインメント/音楽好き/アニメソング","メディア、エンターテインメント/音楽好き/クラシック音楽","メディア、エンターテインメント/音楽好き/フェス好き","メディア、エンターテインメント/音楽好き/ワールドミュージック","メディア、エンターテインメント/音楽好き/邦楽ロック、ポップス","メディア、エンターテインメント/音楽好き/洋楽ロック、ポップス","メディア、エンターテインメント/芸人好き","メディア、エンターテインメント/雑誌好き","メディア、エンターテインメント/占い好き","メディア、エンターテインメント/読書好き","メディア、エンターテインメント/マンガアプリ好き",
  "ライフスタイル、趣味","ライフスタイル、趣味/SUV好き","ライフスタイル、趣味/アート好き","ライフスタイル、趣味/アウトドア好き","ライフスタイル、趣味/アクセサリー好き","ライフスタイル、趣味/アンティーク好き","ライフスタイル、趣味/エコカー好き","ライフスタイル、趣味/オープンカー好き","ライフスタイル、趣味/カメラ好き","ライフスタイル、趣味/ギャンブル好き","ライフスタイル、趣味/コンパクトカー好き","ライフスタイル、趣味/スポーツカー好き","ライフスタイル、趣味/セダン好き","ライフスタイル、趣味/バイク好き","ライフスタイル、趣味/バッグ好き","ライフスタイル、趣味/ハッチバック・ワゴン好き","ライフスタイル、趣味/ファッション好き","ライフスタイル、趣味/ペット愛好者","ライフスタイル、趣味/ペット愛好者/犬","ライフスタイル、趣味/ペット愛好者/猫","ライフスタイル、趣味/ミニバン・ワンボックス好き","ライフスタイル、趣味/メンズファッション好き","ライフスタイル、趣味/レディースファッション好き","ライフスタイル、趣味/育児に関心がある人","ライフスタイル、趣味/歌唱・楽器好き","ライフスタイル、趣味/軽自動車好き","ライフスタイル、趣味/懸賞好き","ライフスタイル、趣味/工芸好き","ライフスタイル、趣味/高級車好き","ライフスタイル、趣味/国産車好き","ライフスタイル、趣味/自動車好き","ライフスタイル、趣味/釣り好き","ライフスタイル、趣味/鉄道好き","ライフスタイル、趣味/輸入車好き",
  "求人","求人/アルバイトに関心がある","求人/新卒採用に関心がある","求人/転職に関心がある",
  "教育","教育/高校受験に関心がある","教育/社会人学習に関心がある","教育/大学受験・専門学校に関心がある","教育/中学受験に関心がある",
  "銀行、金融","銀行、金融/クレジットカードに関心がある","銀行、金融/投資家","銀行、金融/保険に関心がある",
  "美容、健康","美容、健康/スキンケアに関心がある","美容、健康/スパ・リラクゼーションに関心がある","美容、健康/ダイエットに関心がある","美容、健康/ヘアケアに関心がある","美容、健康/メンズコスメに関心がある","美容、健康/レディースコスメに関心がある","美容、健康/健康志向な人","美容、健康/香水好き","美容、健康/美容通",
  "不動産","不動産/マンション","不動産/マンション/新築マンションに関心がある","不動産/マンション/中古マンションに関心がある","不動産/戸建て","不動産/戸建て/新築戸建てに関心がある","不動産/戸建て/中古戸建てに関心がある","不動産/賃貸に関心がある","不動産/売却に関心がある",
  "旅行","旅行/テーマパーク好き","旅行/出張の多い人","旅行/旅行好き","旅行/旅行好き/スノーリゾート","旅行/旅行好き/ビーチリゾート","旅行/旅行好き/家族旅行","旅行/旅行好き/海外旅行","旅行/旅行好き/豪華旅行","旅行/旅行好き/国内旅行"
]
const ydaPurchaseList = [
  "DIY、工具","DIY、工具/材料、部品","DIY、工具/住宅設備","DIY、工具/住宅設備/キッチン","DIY、工具/住宅設備/トイレ","DIY、工具/住宅設備/換気扇","DIY、工具/住宅設備/水回り、配管","DIY、工具/住宅設備/浴室、浴槽、洗面所","DIY、工具/道具、工具",
  "アウトドア、釣り、旅行用品","アウトドア、釣り、旅行用品/アウトドア、キャンプ、登山","アウトドア、釣り、旅行用品/自転車","アウトドア、釣り、旅行用品/釣り","アウトドア、釣り、旅行用品/旅行用品",
  "アパレル、アクセサリー","アパレル、アクセサリー/メンズファッション","アパレル、アクセサリー/メンズファッション/コート、アウター","アパレル、アクセサリー/メンズファッション/ジャケット","アパレル、アクセサリー/メンズファッション/スーツ、フォーマル","アパレル、アクセサリー/メンズファッション/スポーツウエア","アパレル、アクセサリー/メンズファッション/トップス","アパレル、アクセサリー/メンズファッション/ボトムス、パンツ","アパレル、アクセサリー/メンズファッション/メンズシューズ","アパレル、アクセサリー/メンズファッション/メンズバッグ","アパレル、アクセサリー/メンズファッション/ラグジュアリーブランド","アパレル、アクセサリー/メンズファッション/下着、靴下、部屋着","アパレル、アクセサリー/メンズファッション/財布、ファッション小物","アパレル、アクセサリー/メンズファッション/水着","アパレル、アクセサリー/レディースファッション","アパレル、アクセサリー/レディースファッション/コート、アウター","アパレル、アクセサリー/レディースファッション/ジャケット","アパレル、アクセサリー/レディースファッション/スーツ、フォーマル","アパレル、アクセサリー/レディースファッション/スポーツウエア","アパレル、アクセサリー/レディースファッション/トップス","アパレル、アクセサリー/レディースファッション/ドレス、ブライダル","アパレル、アクセサリー/レディースファッション/ボトムス","アパレル、アクセサリー/レディースファッション/ラグジュアリーブランド","アパレル、アクセサリー/レディースファッション/レディースシューズ","アパレル、アクセサリー/レディースファッション/レディースバッグ","アパレル、アクセサリー/レディースファッション/下着、靴下、部屋着","アパレル、アクセサリー/レディースファッション/財布、ファッション小物","アパレル、アクセサリー/レディースファッション/水着","アパレル、アクセサリー/時計、宝石","アパレル、アクセサリー/時計、宝石/ジュエリー","アパレル、アクセサリー/時計、宝石/結婚指輪、婚約指輪","アパレル、アクセサリー/時計、宝石/腕時計",
  "ギフト","ギフト/お中元、お歳暮","ギフト/ギフト","ギフト/パーティー用品",
  "ゲーム、エンターテインメント","ゲーム、エンターテインメント/アニメ","ゲーム、エンターテインメント/イベント、興行チケット","ゲーム、エンターテインメント/イベント、興行チケット/スポーツ","ゲーム、エンターテインメント/イベント、興行チケット/映画館","ゲーム、エンターテインメント/イベント、興行チケット/音楽、ライブ","ゲーム、エンターテインメント/ゲーム、おもちゃ","ゲーム、エンターテインメント/ゲーム、おもちゃ/おもちゃ","ゲーム、エンターテインメント/ゲーム、おもちゃ/テレビゲーム","ゲーム、エンターテインメント/ゲーム、おもちゃ/テレビゲーム/ゲームソフト","ゲーム、エンターテインメント/ゲーム、おもちゃ/テレビゲーム/プレイステーション","ゲーム、エンターテインメント/ゲーム、おもちゃ/テレビゲーム/任天堂のゲーム機","ゲーム、エンターテインメント/ゲーム、おもちゃ/フィギュア","ゲーム、エンターテインメント/ゲーム、おもちゃ/マンガ","ゲーム、エンターテインメント/音楽配信サービス","ゲーム、エンターテインメント/動画配信サービス",
  "コスメ、美容、ヘアケア","コスメ、美容、ヘアケア/スキンケア、フェイスケア商品","コスメ、美容、ヘアケア/スパ、美容サービス","コスメ、美容、ヘアケア/バス、ボディー商品","コスメ、美容、ヘアケア/ヘアケア商品","コスメ、美容、ヘアケア/メイク、化粧品","コスメ、美容、ヘアケア/育毛・増毛","コスメ、美容、ヘアケア/香水、香料","コスメ、美容、ヘアケア/脱毛","コスメ、美容、ヘアケア/日焼け対策商品",
  "コンピューター、周辺機器","コンピューター、周辺機器/PCパーツ、コンピューター用アクセサリー","コンピューター、周辺機器/インターネット回線","コンピューター、周辺機器/タブレット端末","コンピューター、周辺機器/ディスプレイ、モニター","コンピューター、周辺機器/デスクトップパソコン","コンピューター、周辺機器/ノートパソコン","コンピューター、周辺機器/プリンター、スキャナー、FAX",
  "スポーツ、フィットネス","スポーツ、フィットネス/スポーツジム、フィットネスクラブ","スポーツ、フィットネス/スポーツ用品","スポーツ、フィットネス/スポーツ用品/ウインタースポーツ用品","スポーツ、フィットネス/スポーツ用品/ゴルフ用品","スポーツ、フィットネス/スポーツ用品/マラソン、ランニング用品","スポーツ、フィットネス/フィットネス商品",
  "ソフトウエア","ソフトウエア/アンチウイルス、セキュリティソフト","ソフトウエア/オフィス、ビジネスソフト","ソフトウエア/業務管理、会計ソフト","ソフトウエア/動画、画像、音楽ソフト",
  "ダイエット、健康","ダイエット、健康/コンタクトレンズ、ケア用品","ダイエット、健康/サプリメント","ダイエット、健康/ダイエット","ダイエット、健康/花粉症対策","ダイエット、健康/健康飲料、健康食品",
  "ビジネスサービス","ビジネスサービス/クラウドサービス","ビジネスサービス/広告、マーケティングサービス",
  "ペット、ペット用品","ペット、ペット用品/ペット向けサービス","ペット、ペット用品/ペット購入","ペット、ペット用品/犬用品","ペット、ペット用品/猫用品",
  "飲食店","飲食店/居酒屋","飲食店/寿司屋","飲食店/出前、宅配","飲食店/焼肉屋",
  "家具、インテリア","家具、インテリア/インテリア雑貨","家具、インテリア/オフィス家具","家具、インテリア/カーテン、ブラインド","家具、インテリア/カーペット、ラグ、マット","家具、インテリア/ソファ、ソファベッド","家具、インテリア/テーブル","家具、インテリア/デスク、机","家具、インテリア/テレビ台、キャビネット","家具、インテリア/ベッド、マットレス","家具、インテリア/椅子、スツール、座椅子","家具、インテリア/照明、電球","家具、インテリア/生活支援サービス","家具、インテリア/布団、寝具",
  "家電、スマホ、カメラ","家電、スマホ、カメラ/オーディオ機器","家電、スマホ、カメラ/カメラ","家電、スマホ、カメラ/スマートフォン","家電、スマホ、カメラ/スマートフォン、タブレットアクセサリー、周辺機器","家電、スマホ、カメラ/テレビ","家電、スマホ、カメラ/ビデオカメラ","家電、スマホ、カメラ/ブルーレイ、DVDレコーダー","家電、スマホ、カメラ/携帯キャリアプラン","家電、スマホ、カメラ/健康家電","家電、スマホ、カメラ/生活家電","家電、スマホ、カメラ/生活家電/ウォーターサーバー","家電、スマホ、カメラ/生活家電/エアコン、暖房","家電、スマホ、カメラ/生活家電/コーヒーメーカー、エスプレッソマシン","家電、スマホ、カメラ/生活家電/空気清浄機","家電、スマホ、カメラ/生活家電/炊飯器","家電、スマホ、カメラ/生活家電/洗濯機、乾燥機","家電、スマホ、カメラ/生活家電/掃除機","家電、スマホ、カメラ/生活家電/電子レンジ","家電、スマホ、カメラ/生活家電/冷蔵庫","家電、スマホ、カメラ/美容家電",
  "求人","求人/アルバイト、パート","求人/業種","求人/業種/ITエンジニア（システム開発、SE、インフラ）","求人/業種/Web、インターネット、ゲーム","求人/業種/エンジニア（機械、電気、電子、半導体、制御）","求人/業種/クリエイティブ（メディア、アパレル、デザイン）","求人/業種/コンサルタント、金融、不動産専門職","求人/業種/医薬、食品、化学、素材","求人/業種/医療、福祉、介護","求人/業種/営業","求人/業種/企画、マーケティング、経営","求人/業種/技能工、設備、運輸、農林水産","求人/業種/教育、保育","求人/業種/建築・土木","求人/業種/公務員","求人/業種/事務、管理","求人/業種/販売、フード","求人/新卒採用","求人/派遣",
  "教育","教育/語学","教育/語学/その他言語","教育/語学/英語","教育/高校受験","教育/資格","教育/大学受験","教育/中学受験",
  "金融","金融/クレジットカード","金融/ふるさと納税","金融/モバイルペイメント","金融/銀行サービス","金融/税金","金融/電子マネー","金融/投資","金融/投資/NISA","金融/投資/為替","金融/投資/株式","金融/投資/金","金融/投資/投資信託","金融/年金","金融/保険","金融/保険/医療保険","金融/保険/火災保険","金融/保険/学資保険","金融/保険/自動車保険","金融/保険/傷害保険","金融/保険/生命保険","金融/保険/旅行保険","金融/融資","金融/融資/事業者ローン","金融/融資/自動車ローン","金融/融資/住宅ローン","金融/融資/消費者ローン",
  "行事","行事/挙式、披露宴プラン",
  "自動車、バイク","自動車、バイク/バイク","自動車、バイク/バイク/海外メーカー","自動車、バイク/バイク/国産メーカー","自動車、バイク/バイク/新車","自動車、バイク/バイク/中古車","自動車、バイク/バイク/排気量別","自動車、バイク/バイク/排気量別/原動機付自転車、小型自動二輪車","自動車、バイク/バイク/排気量別/大型自動二輪車","自動車、バイク/バイク/排気量別/普通自動二輪車（中型）","自動車、バイク/運転免許","自動車、バイク/査定","自動車、バイク/査定/バイク査定","自動車、バイク/査定/自動車査定","自動車、バイク/自動車","自動車、バイク/自動車/ボディータイプ","自動車、バイク/自動車/ボディータイプ/SUV","自動車、バイク/自動車/ボディータイプ/SUV/クロスカントリーSUV","自動車、バイク/自動車/ボディータイプ/SUV/コンパクトSUV","自動車、バイク/自動車/ボディータイプ/SUV/都市型SUV","自動車、バイク/自動車/ボディータイプ/エコカー","自動車、バイク/自動車/ボディータイプ/エコカー/ハイブリッドカー","自動車、バイク/自動車/ボディータイプ/エコカー/電気自動車","自動車、バイク/自動車/ボディータイプ/オープンカー","自動車、バイク/自動車/ボディータイプ/クーペ、スポーツカー","自動車、バイク/自動車/ボディータイプ/コンパクトカー","自動車、バイク/自動車/ボディータイプ/ステーションワゴン","自動車、バイク/自動車/ボディータイプ/セダン","自動車、バイク/自動車/ボディータイプ/ハッチバック","自動車、バイク/自動車/ボディータイプ/ミニバン、ワンボックス","自動車、バイク/自動車/ボディータイプ/軽自動車","自動車、バイク/自動車/価格帯","自動車、バイク/自動車/価格帯/高級車","自動車、バイク/自動車/価格帯/中級車","自動車、バイク/自動車/価格帯/低価格車","自動車、バイク/自動車/商用車","自動車、バイク/自動車/新車","自動車、バイク/自動車/生産国","自動車、バイク/自動車/生産国/国産車","自動車、バイク/自動車/生産国/輸入車","自動車、バイク/自動車/中古車","自動車、バイク/自動車/販売店","自動車、バイク/自動車パーツ、アクセサリー","自動車、バイク/自動車パーツ、アクセサリー/ETC、レーザー探知機、ドライブレコーダー","自動車、バイク/自動車パーツ、アクセサリー/オイル、バッテリー、メンテナンス用品","自動車、バイク/自動車パーツ、アクセサリー/カーナビ、カーオーディオ","自動車、バイク/自動車パーツ、アクセサリー/タイヤ、ホイール","自動車、バイク/自動車パーツ、アクセサリー/ライト、レンズ","自動車、バイク/自動車パーツ、アクセサリー/車用内装パーツ","自動車、バイク/車検",
  "食品、飲料","食品、飲料/レシピ","食品、飲料/飲料","食品、飲料/飲料/お酒","食品、飲料/飲料/お酒/ビール、発泡酒","食品、飲料/飲料/お酒/ワイン","食品、飲料/飲料/お酒/焼酎","食品、飲料/飲料/お酒/日本酒","食品、飲料/飲料/お酒/洋酒","食品、飲料/飲料/ソフトドリンク、ジュース","食品、飲料/食品",
  "不動産","不動産/リフォーム（修繕、改良）","不動産/引っ越し","不動産/地域","不動産/地域/沖縄","不動産/地域/関東地方","不動産/地域/関東地方/茨城","不動産/地域/関東地方/群馬","不動産/地域/関東地方/埼玉","不動産/地域/関東地方/山梨","不動産/地域/関東地方/神奈川","不動産/地域/関東地方/千葉","不動産/地域/関東地方/東京","不動産/地域/関東地方/栃木","不動産/地域/近畿地方","不動産/地域/近畿地方/京都","不動産/地域/近畿地方/滋賀","不動産/地域/近畿地方/大阪","不動産/地域/近畿地方/奈良","不動産/地域/近畿地方/兵庫","不動産/地域/近畿地方/和歌山","不動産/地域/九州地方","不動産/地域/九州地方/宮崎","不動産/地域/九州地方/熊本","不動産/地域/九州地方/佐賀","不動産/地域/九州地方/鹿児島","不動産/地域/九州地方/大分","不動産/地域/九州地方/長崎","不動産/地域/九州地方/福岡","不動産/地域/四国地方","不動産/地域/四国地方/愛媛","不動産/地域/四国地方/香川","不動産/地域/四国地方/高知","不動産/地域/四国地方/徳島","不動産/地域/信越地方","不動産/地域/信越地方/新潟","不動産/地域/信越地方/長野","不動産/地域/中国地方","不動産/地域/中国地方/岡山","不動産/地域/中国地方/広島","不動産/地域/中国地方/山口","不動産/地域/中国地方/鳥取","不動産/地域/中国地方/島根","不動産/地域/東海地方","不動産/地域/東海地方/愛知","不動産/地域/東海地方/岐阜","不動産/地域/東海地方/三重","不動産/地域/東海地方/静岡","不動産/地域/東北地方","不動産/地域/東北地方/岩手","不動産/地域/東北地方/宮城","不動産/地域/東北地方/山形","不動産/地域/東北地方/秋田","不動産/地域/東北地方/青森","不動産/地域/東北地方/福島","不動産/地域/北海道","不動産/地域/北陸地方","不動産/地域/北陸地方/石川","不動産/地域/北陸地方/富山","不動産/地域/北陸地方/福井","不動産/注文住宅","不動産/賃貸（オフィス）","不動産/賃貸（マンション、戸建て）","不動産/土地","不動産/不動産購入","不動産/不動産購入/マンション","不動産/不動産購入/マンション/新築マンション","不動産/不動産購入/マンション/中古マンション","不動産/不動産購入/戸建て","不動産/不動産購入/戸建て/新築戸建て","不動産/不動産購入/戸建て/中古戸建て","不動産/不動産売却",
  "幼児、子供向け製品","幼児、子供向け製品/おむつ、トイレ用品","幼児、子供向け製品/バッグ、ランドセル","幼児、子供向け製品/ベビーカー","幼児、子供向け製品/ベビーシート、チャイルドシート","幼児、子供向け製品/ベビー家具","幼児、子供向け製品/ベビー服、シューズ","幼児、子供向け製品/子供服、シューズ","幼児、子供向け製品/授乳、食事用品","幼児、子供向け製品/保育園、幼稚園、託児所","幼児、子供向け製品/抱っこひも、おんぶひも",
  "旅行、交通","旅行、交通/バス","旅行、交通/ホテル、宿泊施設","旅行、交通/レンタカー","旅行、交通/海外旅行","旅行、交通/海外旅行/アジア","旅行、交通/海外旅行/アジア/韓国","旅行、交通/海外旅行/アジア/香港、マカオ","旅行、交通/海外旅行/アジア/台湾","旅行、交通/海外旅行/アジア/中国","旅行、交通/海外旅行/アジア/東南アジア","旅行、交通/海外旅行/アジア/南アジア","旅行、交通/海外旅行/アメリカ、カナダ","旅行、交通/海外旅行/オセアニア","旅行、交通/海外旅行/グアム、サイパン","旅行、交通/海外旅行/ハワイ","旅行、交通/海外旅行/ヨーロッパ","旅行、交通/海外旅行/中東、アフリカ","旅行、交通/海外旅行/中南米、カリブ海地域","旅行、交通/記念日旅行","旅行、交通/航空チケット","旅行、交通/航空チケット/海外","旅行、交通/航空チケット/国内","旅行、交通/国内旅行","旅行、交通/国内旅行/沖縄","旅行、交通/国内旅行/関東地方","旅行、交通/国内旅行/近畿地方","旅行、交通/国内旅行/九州地方","旅行、交通/国内旅行/四国地方","旅行、交通/国内旅行/信越地方","旅行、交通/国内旅行/中国地方","旅行、交通/国内旅行/東海地方","旅行、交通/国内旅行/東北地方","旅行、交通/国内旅行/北海道","旅行、交通/国内旅行/北陸地方","旅行、交通/船、フェリー、クルーズ","旅行、交通/電車","旅行、交通/日帰り旅行"
]
const ydaAttributeList = [
  "ライフイベント","ライフイベント/マイホームの購入","ライフイベント/マイホームの購入/マイホームを近々購入予定、最近購入した","ライフイベント/引っ越し","ライフイベント/引っ越し/近々引っ越し予定、最近引っ越した","ライフイベント/結婚","ライフイベント/結婚/近々結婚予定、最近結婚した","ライフイベント/就職","ライフイベント/就職/近々就職予定、最近就職した","ライフイベント/出産","ライフイベント/出産/子供が近々生まれる予定、最近子供が生まれた","ライフイベント/退職","ライフイベント/退職/近々退職予定、最近退職した","ライフイベント/大学卒業","ライフイベント/大学卒業/近々卒業予定、最近卒業した","ライフイベント/転職","ライフイベント/転職/近々転職予定、最近転職した",
  "家族構成","家族構成/子供の有無","家族構成/子供の有無/子供あり","家族構成/子供の有無/子供あり/高校生","家族構成/子供の有無/子供あり/社会人","家族構成/子供の有無/子供あり/小学生","家族構成/子供の有無/子供あり/大学生またはその他学生","家族構成/子供の有無/子供あり/中学生","家族構成/子供の有無/子供あり/未就学児（0歳）","家族構成/子供の有無/子供あり/未就学児（1〜3歳）","家族構成/子供の有無/子供あり/未就学児（4〜6歳）","家族構成/子供の有無/子供なし","家族構成/同居している親がいる","家族構成/同居している祖父母がいる","家族構成/同居している孫がいる","家族構成/配偶者の有無","家族構成/配偶者の有無/既婚","家族構成/配偶者の有無/独身",
  "学歴","学歴/最終学歴","学歴/最終学歴/高校卒","学歴/最終学歴/大学、専門学校卒","学歴/最終学歴/大学院卒",
  "携帯電話","携帯電話/auを利用している","携帯電話/NTT ドコモを利用している","携帯電話/Softbankを利用している","携帯電話/一定期間内でキャリアを変更していない","携帯電話/一定期間内でキャリアを変更している","携帯電話/楽天モバイルを利用している",
  "個人年収","個人年収/1,000万円以上","個人年収/600万円以上800万円未満","個人年収/800万円以上1,000万円未満",
  "仕事","仕事/業種","仕事/業種/IT、通信、インターネット関連","仕事/業種/サービス","仕事/業種/医療 (医師・看護師・薬剤師など)","仕事/業種/飲食","仕事/業種/運輸・流通","仕事/業種/官公庁・公務員","仕事/業種/金融(銀行・証券・保険など)","仕事/業種/広告・マスコミ・出版・放送","仕事/業種/小売","仕事/業種/製造","仕事/業種/農林水産","仕事/業種/不動産、建設","仕事/業種/福祉 (社会福祉・介護福祉・ケースワーカーなど)","仕事/業種/保育(保育園・幼稚園・託児所・ベビーシッターなど)","仕事/業種/法律、会計","仕事/業種/理容・美容・エステティック","仕事/職業","仕事/職業/その他学生","仕事/職業/パート、アルバイト","仕事/職業/会社員（契約社員、派遣社員）","仕事/職業/会社員（正社員）","仕事/職業/経営者、会社役員","仕事/職業/公務員","仕事/職業/自営業、自由業","仕事/職業/専業主婦（主夫）","仕事/職業/大学生、大学院生","仕事/職業/無職",
  "世帯資産","世帯資産/1,000万円以上5,000万円未満","世帯資産/5,000万円以上",
  "世帯年収","世帯年収/1,000万円以上1,500万円未満","世帯年収/1,500万円以上","世帯年収/600万円以上800万円未満","世帯年収/800万円以上1,000万円未満",
  "誕生日","誕生日/10月に誕生日を迎える","誕生日/11月に誕生日を迎える","誕生日/12月に誕生日を迎える","誕生日/1月に誕生日を迎える","誕生日/2月に誕生日を迎える","誕生日/3月に誕生日を迎える","誕生日/4月に誕生日を迎える","誕生日/5月に誕生日を迎える","誕生日/6月に誕生日を迎える","誕生日/7月に誕生日を迎える","誕生日/8月に誕生日を迎える","誕生日/9月に誕生日を迎える"
]
const ygTargetingListMapExtended = {
  ...ygTargetingListMap,
  '興味関心': ydaInterestList,
  '購買意向': ydaPurchaseList,
  '属性・ライフイベント': ydaAttributeList
}
const ygCurrentTargetingList = (targetingType) => {
  return ygTargetingListMapExtended[targetingType] || []
}
/* --- YG デバイスのALL排他制御 --- */
const toggleYgDevice = (value) => {
  if (ygIsDeviceFixed(editingYgPattern.value.menu)) return
  if (value === 'ALL') {
    editingYgPattern.value.device = ['ALL']
    return
  }
  const next = editingYgPattern.value.device.filter(v => v !== 'ALL')
  const idx = next.indexOf(value)
  if (idx >= 0) {
    next.splice(idx, 1)
  } else {
    next.push(value)
  }
  editingYgPattern.value.device = next.length ? next : ['ALL']
}
/* --- YG メニュー変更時の連動処理 --- */
const onYgMenuChange = () => {
  const menu = editingYgPattern.value.menu
  editingYgPattern.value.billing = ygIsBillingFixed(menu) ? ygBillingOptions(menu)[0] : ''
  editingYgPattern.value.videoDuration = ygIsVideoMenu(menu) ? editingYgPattern.value.videoDuration : ''
  editingYgPattern.value.device = ygDeviceOptions(menu).includes('ALL') ? ['ALL'] : [ygDeviceOptions(menu)[0]]
  editingYgPattern.value.age1 = 'ALL'
  editingYgPattern.value.age2 = ''
  editingYgPattern.value.age3 = '';
  [1, 2, 3].forEach(n => {
    editingYgPattern.value[`targeting${n}Type`] = 'なし'
    editingYgPattern.value[`targeting${n}Values`] = []
  })
}
const isYgAge2Disabled = computed(() => {
  return editingYgPattern.value.age1 === 'ALL' || !editingYgPattern.value.age1
})
const isYgAge3Disabled = computed(() => {
  return editingYgPattern.value.age1 === 'ALL' || !editingYgPattern.value.age1
})
/* --- 年齢①変更時：①＞②の矛盾を防ぐため、無効になった②はリセット --- */
const onYgAge1Change = () => {
  const pattern = editingYgPattern.value
  if (pattern.age1 === 'ALL') {
    pattern.age2 = ''
    return
  }
  const validAges = ygAge2Options(pattern.menu, pattern.age1)
  if (pattern.age2 && !validAges.includes(pattern.age2)) {
    pattern.age2 = ''
  }
}
/* --- YG 予算：¥表示の自動フォーマット --- */
const editingYgBudgetDisplay = computed({
  get: () => {
    const val = editingYgPattern.value.budget
    return val ? `¥${Number(val).toLocaleString()}` : ''
  },
  set: (val) => {
    const digits = String(val).replace(/[^\d]/g, '')
    editingYgPattern.value.budget = digits ? Number(digits) : null
  }
})
/* --- YG 概要テーブル表示用フォーマット関数 --- */
const formatYgPeriod = (pattern) => {
  return pattern.periodNumber ? `${pattern.periodNumber}${pattern.periodUnit}` : '未設定'
}
const formatYgBudget = (pattern) => {
  return pattern.budget ? `¥${Number(pattern.budget).toLocaleString()}` : '未設定'
}
const formatYgAge = (pattern) => {
  if (!pattern.age1 || pattern.age1 === 'ALL') return 'ALL'
  return pattern.age2 ? `${pattern.age1}${pattern.age2}` : pattern.age1
}
const formatYgArea = (pattern) => {
  if (!pattern.prefNames || !pattern.prefNames.length) return '未設定'
  const base = pattern.prefNames.join(' / ')
  return pattern.city ? `${base}・${pattern.city}` : base
}
const formatYgTargeting = (pattern, n) => {
  const type = pattern[`targeting${n}Type`]
  const values = pattern[`targeting${n}Values`]
  if (!type || type === 'なし') return '未設定'
  if (!values || !values.length) return '未設定'
  if (ygTargetingFreeTextTypes.includes(type)) {
    const text = values[0] || ''
    return text.length > 24 ? text.slice(0, 24) + '...' : text
  }
  return values.join(' / ')
}
/* --- YG ターゲティングモーダル（3枠共通で利用） --- */
const ygTargetModalOpen = ref(false)
const ygTargetRowIndex = ref(null)
const ygTargetSlot = ref(1)
const ygTargetSearch = ref('')
const ygPendingTargetNames = ref([])
const ygPendingTargetText = ref('')
const ygIsFreeTextTargetingType = computed(() => {
  const type = editingYgPattern.value[`targeting${ygTargetSlot.value}Type`]
  return ygTargetingFreeTextTypes.includes(type)
})
const ygTargetModalTitle = computed(() => {
  const type = editingYgPattern.value[`targeting${ygTargetSlot.value}Type`]
  if (ygIsFreeTextTargetingType.value) return `${type} - 内容入力`
  return `${type}を選択`
})
const ygFilteredTargetOptions = computed(() => {
  const type = editingYgPattern.value[`targeting${ygTargetSlot.value}Type`]
  const q = ygTargetSearch.value.trim().toLowerCase()
  const list = ygCurrentTargetingList(type)
  return q ? list.filter(v => v.toLowerCase().includes(q)) : list
})
const openYgTargetModal = (slot) => {
  ygTargetSlot.value = slot
  const type = editingYgPattern.value[`targeting${slot}Type`]
  const currentValues = editingYgPattern.value[`targeting${slot}Values`] || []
  if (ygTargetingFreeTextTypes.includes(type)) {
    ygPendingTargetText.value = currentValues[0] || ''
  } else {
    ygPendingTargetNames.value = [...currentValues]
  }
  ygTargetSearch.value = ''
  ygTargetModalOpen.value = true
}
const closeYgTargetModal = () => {
  ygTargetModalOpen.value = false
  ygTargetSearch.value = ''
}
const clearYgTargetSelection = () => {
  ygPendingTargetNames.value = []
}
const toggleYgTarget = (value) => {
  const idx = ygPendingTargetNames.value.indexOf(value)
  if (idx >= 0) {
    ygPendingTargetNames.value.splice(idx, 1)
  } else {
    ygPendingTargetNames.value.push(value)
  }
}
const removeYgTarget = (value) => {
  ygPendingTargetNames.value = ygPendingTargetNames.value.filter(v => v !== value)
}
const confirmYgTargetSelection = () => {
  const type = editingYgPattern.value[`targeting${ygTargetSlot.value}Type`]
  if (ygTargetingFreeTextTypes.includes(type)) {
    const trimmed = ygPendingTargetText.value.trim()
    editingYgPattern.value[`targeting${ygTargetSlot.value}Values`] = trimmed ? [trimmed] : []
  } else {
    editingYgPattern.value[`targeting${ygTargetSlot.value}Values`] = [...ygPendingTargetNames.value]
  }
  closeYgTargetModal()
}
const onYgTargetingTypeChange = (slot) => {
  editingYgPattern.value[`targeting${slot}Values`] = []
}
/* --- YG 編集モーダルの状態管理 --- */
const isYgEditModalOpen = ref(false)
const ygEditingIndex = ref(null)
const editingYgPattern = ref({})
const openYgEditModal = (pattern, index) => {
  ygEditingIndex.value = index
  editingYgPattern.value = JSON.parse(JSON.stringify(pattern))
  activeFieldPopover.value = null
  isYgEditModalOpen.value = true
}
const closeYgEditModal = () => {
  isYgEditModalOpen.value = false
  ygEditingIndex.value = null
  editingYgPattern.value = {}
  activeFieldPopover.value = null
}
const saveYgEditModal = () => {
  if (ygEditingIndex.value !== null && ygEditingIndex.value >= 0 && ygEditingIndex.value < ygPatterns.length) {
    ygPatterns.splice(ygEditingIndex.value, 1, JSON.parse(JSON.stringify(editingYgPattern.value)))
  }
  closeYgEditModal()
}
const addYgPattern = () => {
  const newPattern = createEmptyYgPattern()
  ygPatterns.push(newPattern)
  openYgEditModal(newPattern, ygPatterns.length - 1)
}
const removeYgPattern = (index) => {
  if (ygPatterns.length > 1) {
    ygPatterns.splice(index, 1)
  }
}
const copyYgPattern = (index) => {
  const source = ygPatterns[index]
  const copy = JSON.parse(JSON.stringify(source))
  copy.id = `yg-${Date.now()}-${ygPatternSeq++}`
  ygPatterns.splice(index + 1, 0, copy)
  openYgEditModal(copy, index + 1)
}

/* =========================================================
   Listing: 依頼情報 ＋ パターン設定データ（GAS/ListingForm.htmlを再現）
========================================================= */
/* --- 依頼情報（案件単位で1回だけ入力） --- */
const listingInfo = reactive({
  requestTypes: [],
  lp: '',
  kwBroad: '',
  kwPhrase: '',
  kwExact: ''
})
const listingRequestTypeOptions = ['SIM', 'キーワード案作成', 'TD考案']
const toggleListingRequestType = (value) => {
  const idx = listingInfo.requestTypes.indexOf(value)
  if (idx >= 0) {
    listingInfo.requestTypes.splice(idx, 1)
  } else {
    listingInfo.requestTypes.push(value)
  }
}

/* --- パターン行 --- */
let listingPatternSeq = 1
const createEmptyListingPattern = () => ({
  id: `listing-${Date.now()}-${listingPatternSeq++}`,
  menu: '',
  periodNumber: 1,
  periodUnit: 'ヶ月間',
  budget: null,
  prefId: 'national',
  prefName: '全国',
  prefNames: ['全国'],
  city: '',
  gender: 'ALL',
  age1: 'ALL',
  age2: '',
  age3: '',
  device: ['ALL'],
  remarks: ''
})
const listingPatterns = reactive([createEmptyListingPattern()])
const listingMenuOptions = ['GKT', 'YSS', 'MSA']
const listingAge1Options = ['ALL', '18～', '25～', '35～', '45～', '55～', '65歳以上']
const listingAge2Options = (age1) => {
  const all = ['24歳', '34歳', '44歳', '54歳', '64歳', '以上すべて']
  if (!age1 || age1 === 'ALL') return all
  const startAge = parseInt(String(age1).replace(/[^0-9]/g, ''), 10)
  return all.filter(v => v === '以上すべて' || parseInt(v.replace(/[^0-9]/g, ''), 10) >= startAge)
}
const listingDeviceOptions = ['ALL', 'PC', 'TB', 'SP']
/* --- Listing デバイスのALL排他制御 --- */
const toggleListingDevice = (value) => {
  if (value === 'ALL') {
    editingListingPattern.value.device = ['ALL']
    return
  }
  const next = editingListingPattern.value.device.filter(v => v !== 'ALL')
  const idx = next.indexOf(value)
  if (idx >= 0) {
    next.splice(idx, 1)
  } else {
    next.push(value)
  }
  editingListingPattern.value.device = next.length ? next : ['ALL']
}
/* --- メニュー変更時の連動処理（YSSは性別・年齢①をALL固定） --- */
const onListingMenuChange = () => {
  const menu = editingListingPattern.value.menu
  editingListingPattern.value.gender = 'ALL'
  editingListingPattern.value.age1 = 'ALL'
  editingListingPattern.value.age2 = ''
  editingListingPattern.value.age3 = ''
}
/* --- 年齢①変更時：②③の有効/無効切り替え、逆転する②はリセット --- */
const onListingAge1Change = () => {
  const pattern = editingListingPattern.value
  if (pattern.age1 === 'ALL') {
    pattern.age2 = ''
    pattern.age3 = ''
    return
  }
  const validAges = listingAge2Options(pattern.age1)
  if (pattern.age2 && !validAges.includes(pattern.age2)) {
    pattern.age2 = ''
  }
}
const isListingAge2Disabled = computed(() => {
  return editingListingPattern.value.age1 === 'ALL' || !editingListingPattern.value.age1
})
const isListingAge3Disabled = computed(() => {
  return editingListingPattern.value.age1 === 'ALL' || !editingListingPattern.value.age1
})
/* --- 予算：¥表示の自動フォーマット --- */
const editingListingBudgetDisplay = computed({
  get: () => {
    const val = editingListingPattern.value.budget
    return val ? `¥${Number(val).toLocaleString()}` : ''
  },
  set: (val) => {
    const digits = String(val).replace(/[^\d]/g, '')
    editingListingPattern.value.budget = digits ? Number(digits) : null
  }
})
/* --- 概要テーブル表示用フォーマット関数 --- */
const formatListingPeriod = (pattern) => {
  return pattern.periodNumber ? `${pattern.periodNumber}${pattern.periodUnit}` : '未設定'
}
const formatListingBudget = (pattern) => {
  return pattern.budget ? `¥${Number(pattern.budget).toLocaleString()}` : '未設定'
}
const formatListingAge = (pattern) => {
  if (!pattern.age1 || pattern.age1 === 'ALL') return 'ALL'
  return pattern.age2 ? `${pattern.age1}${pattern.age2}` : pattern.age1
}
const formatListingArea = (pattern) => {
  if (!pattern.prefNames || !pattern.prefNames.length) return '未設定'
  const base = pattern.prefNames.join(' / ')
  return pattern.city ? `${base}・${pattern.city}` : base
}
/* --- 編集モーダルの状態管理 --- */
const isListingEditModalOpen = ref(false)
const listingEditingIndex = ref(null)
const editingListingPattern = ref({})
const openListingEditModal = (pattern, index) => {
  listingEditingIndex.value = index
  editingListingPattern.value = JSON.parse(JSON.stringify(pattern))
  activeFieldPopover.value = null
  isListingEditModalOpen.value = true
}
const closeListingEditModal = () => {
  isListingEditModalOpen.value = false
  listingEditingIndex.value = null
  editingListingPattern.value = {}
  activeFieldPopover.value = null
}
const saveListingEditModal = () => {
  if (listingEditingIndex.value !== null && listingEditingIndex.value >= 0 && listingEditingIndex.value < listingPatterns.length) {
    listingPatterns.splice(listingEditingIndex.value, 1, JSON.parse(JSON.stringify(editingListingPattern.value)))
  }
  closeListingEditModal()
}
const addListingPattern = () => {
  const newPattern = createEmptyListingPattern()
  listingPatterns.push(newPattern)
  openListingEditModal(newPattern, listingPatterns.length - 1)
}
const removeListingPattern = (index) => {
  if (listingPatterns.length > 1) {
    listingPatterns.splice(index, 1)
  }
}
const copyListingPattern = (index) => {
  const source = listingPatterns[index]
  const copy = JSON.parse(JSON.stringify(source))
  copy.id = `listing-${Date.now()}-${listingPatternSeq++}`
  listingPatterns.splice(index + 1, 0, copy)
  openListingEditModal(copy, index + 1)
}

const isSubmitting = ref(false)
// 送信ペイロード用に、各媒体の選択状態へ「marginType（通常/イレギュラー）」「marginValue（合計マージン率）」を
// 付与する。バックエンド（SubmitController）のcampaigns保存がこの2キーを前提にしているため必須。
const buildPlatformsPayload = () => {
  const result = {}
  Object.keys(platforms).forEach((id) => {
    const p = platforms[id]
    result[id] = {
      ...p,
      marginType: p.marginKind === 'irregular' ? 'イレギュラー' : '通常',
      marginValue: platformTotalMarginRate(id)
    }
  })
  return result
}
const submitForm = async () => {
  const payload = {
    entry: formData,
    platforms: buildPlatformsPayload(),
    youtube: {
      patterns: youtubePatterns
    },
    meta: {
      patterns: metaPatterns
    },
    yg: {
      patterns: ygPatterns
    },
    listing: {
      requestTypes: listingInfo.requestTypes,
      lp: listingInfo.lp,
      kwBroad: listingInfo.kwBroad,
      kwPhrase: listingInfo.kwPhrase,
      kwExact: listingInfo.kwExact,
      patterns: listingPatterns
    }
  }
  if (isSubmitting.value) return
  isSubmitting.value = true
  try {
    const response = await fetch('/api/submit', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
    if (!response.ok) {
      // Laravel側が返すエラーメッセージ（例外の内容）があれば、それを表示に含める
      let detail = ''
      try {
        const errorBody = await response.json()
        detail = errorBody.message ? `\n${errorBody.message}` : ''
      } catch (e) {
        // JSONで返ってこない場合（HTMLのエラーページ等）は無視する
      }
      throw new Error(`サーバーエラー（status: ${response.status}）${detail}`)
    }
    const result = await response.json()
    console.log('送信結果:', result)
    alert('送信が完了しました！')
  } catch (error) {
    console.error('送信エラー:', error)
    alert(`送信に失敗しました。\n\n${error.message}`)
  } finally {
    isSubmitting.value = false
  }
}
</script>
<style scoped>
/* =========================
  1. 基本設定・全体レイアウト
========================= */
.sim-form-container {
  font-family: "Barlow", "Noto Sans JP", -apple-system, BlinkMacSystemFont, sans-serif;
  color: #111111;
  background-color: #ffffff;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  box-sizing: border-box;
  width: 100%;
}
.transparent-input,
.transparent-input:-webkit-autofill,
.transparent-input:-webkit-autofill:hover,
.transparent-input:-webkit-autofill:focus,
.transparent-input:-webkit-autofill:active {
  background-color: transparent !important;
  -webkit-text-fill-color: #111111 !important;
  transition: background-color 5000s ease-in-out 0s;
}
/* =========================
  2. ヘッダー
========================= */
.header {
  position: sticky;
  top: 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #f0f0f0;
  padding: 0 2rem;
  flex-shrink: 0;
  z-index: 100;
  background-color: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(8px);
  height: 64px;
  width: 100%;
  box-sizing: border-box;
}
.logo-area {
  display: flex;
  align-items: center;
  gap: 12px;
  font-weight: 700;
}
.company-name {
  font-size: 1rem;
  letter-spacing: -0.02em;
}
.app-title {
  font-size: 1rem;
  color: #888888;
  letter-spacing: 0.05em;
  font-weight: 400;
}
.stepper {
  display: flex;
  height: 100%;
}
.step {
  padding: 0 24px;
  display: flex;
  align-items: center;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  color: #aaaaaa;
  transition: all 0.3s ease;
}
.step.active {
  background-color: #111111;
  color: #ffffff;
}
/* =========================
  3. メイン領域・構造（ワイド化対応）
========================= */
.content-wrapper {
  flex: 1;
  width: 100% !important;
  max-width: 100% !important;
  min-width: 0;
  display: flex;
  justify-content: center;
  padding: 20px 24px 60px 24px !important;
  box-sizing: border-box;
  overflow-x: hidden;
}
.transition-wrapper {
  width: 100%;
  min-width: 0;
}
.step-panel {
  width: 100% !important;
  max-width: 100% !important;
  min-width: 0;
  margin: 0 auto;
  box-sizing: border-box;
}
.step1-content {
  max-width: 560px !important;
  margin: 0 auto;
  padding: 0 32px;
  box-sizing: border-box;
}
.step2-content,
.step3-content {
  width: 100% !important;
  max-width: 100% !important;
  min-width: 0;
  margin: 0 auto;
  box-sizing: border-box;
}
.title-area h2,
.platform-header h2 {
  font-size: 2.2rem;
  font-weight: 800;
  margin: 0;
  letter-spacing: -0.03em;
}
.required-label,
.sub-label {
  font-size: 0.75rem;
  color: #888888;
  display: block;
  margin-top: 6px;
}
/* =========================
  4-0. Login / Register（未ログイン時）
========================= */
.auth-screen {
  display: flex;
  justify-content: center;
  padding: 40px 0 20px;
}
.auth-card {
  width: 100%;
  max-width: 400px;
  display: flex;
  flex-direction: column;
  gap: 18px;
}
.auth-name-row {
  display: flex;
  gap: 20px;
}
.auth-name-row .form-group {
  flex: 1;
  min-width: 0;
}
.auth-logo-row {
  display: flex;
  flex-direction: column;
  gap: 2px;
  margin-bottom: 8px;
}
.auth-logo-row .company-name {
  font-size: 0.8rem;
  color: #888888;
  letter-spacing: 0.02em;
}
.auth-logo-row .app-title {
  font-size: 1.6rem;
  font-weight: 800;
  letter-spacing: -0.02em;
}
.auth-title {
  font-size: 1.4rem;
  font-weight: 700;
  margin: 0;
}
.auth-subtitle {
  font-size: 0.85rem;
  color: #888888;
  margin: -12px 0 0;
}
.auth-submit-btn {
  width: 100%;
  justify-content: center;
  margin-top: 4px;
}
.auth-submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.auth-switch-text {
  font-size: 0.85rem;
  color: #666666;
  text-align: center;
  margin: 0;
}
.auth-switch-link {
  background: none;
  border: none;
  padding: 0;
  margin-left: 4px;
  color: #111111;
  font-weight: 700;
  text-decoration: underline;
  cursor: pointer;
  font-size: inherit;
}
.logged-in-as {
  font-size: 0.85rem;
  color: #666666;
  margin: 8px 0 0;
}
.logged-in-as .auth-switch-link {
  margin-left: 10px;
}
/* =========================
  4. STEP 1（基本フォーム）
========================= */
.form-body-vertical {
  margin-top: 40px;
  display: flex;
  flex-direction: column;
  gap: 40px;
}
.form-group {
  opacity: 1;
  transition: opacity 0.35s ease;
}
.form-group.is-dimmed {
  opacity: 0.25;
}
.form-group label {
  display: flex;
  align-items: center;
  font-weight: 600;
  margin-bottom: 10px;
  color: #111111;
  white-space: nowrap;
}
.num {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 22px;
  height: 22px;
  background-color: #111111;
  color: #ffffff;
  border-radius: 50%;
  font-size: 0.72rem;
  font-weight: 700;
  margin-right: 10px;
  flex-shrink: 0;
}
.req {
  color: #e03131;
  margin-right: 4px;
}
.unified-font {
  font-size: 0.95rem !important;
  line-height: 1.5;
}
.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="password"],
.custom-date-input {
  width: 100%;
  padding: 12px 0;
  border: none;
  border-bottom: 1px solid #e0e0e0;
  color: #111111;
  outline: none;
  transition: border-color 0.3s ease;
  box-sizing: border-box;
}
.custom-textarea {
  width: 100%;
  height: 120px;
  padding: 12px 0;
  border: none;
  border-bottom: 1px solid #e0e0e0;
  border-radius: 0;
  color: #111111;
  outline: none;
  resize: vertical;
  font-family: inherit;
  box-sizing: border-box;
}
.custom-textarea:focus,
.form-group input[type="text"]:focus,
.form-group input[type="email"]:focus,
.form-group input[type="password"]:focus,
.custom-date-input:focus {
  border-bottom: 2px solid #111111;
}
.error-text {
  color: #e03131;
  font-size: 0.75rem;
  margin-top: 6px;
}
.radio-row {
  display: flex;
  gap: 12px;
  margin-top: 6px;
  flex-wrap: wrap;
}
.radio-item {
  flex: 1;
  min-width: 100px;
  display: flex;
  align-items: center;
  padding: 10px 8px;
  border-radius: 6px;
  cursor: pointer;
}
.radio-item input[type="radio"] {
  position: absolute;
  opacity: 0;
}
.custom-radio {
  width: 20px;
  height: 20px;
  border: 2px solid #b0b0b0;
  border-radius: 50%;
  margin-right: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-sizing: border-box;
  transition: border-color 0.2s ease;
}
.custom-radio::after {
  content: '';
  width: 11px;
  height: 11px;
  background-color: #111111;
  border-radius: 50%;
  opacity: 0;
  transform: scale(0.4);
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.radio-item:hover .custom-radio {
  border-color: #111111;
}
.radio-item.is-selected .custom-radio {
  border-color: #111111;
  border-width: 2px;
}
.radio-item.is-selected .custom-radio::after {
  opacity: 1;
  transform: scale(1);
}
.radio-label-text {
  font-size: 0.9rem;
}
/* =========================
  5. STEP 2（プラットフォーム選択）
========================= */
.platform-header {
  margin-bottom: 40px;
}
.platform-grid-container {
  display: flex;
  width: 100%;
  border-top: 1px solid #e8e8e8;
  border-left: 1px solid #e8e8e8;
  box-sizing: border-box;
}
.platform-label-column {
  width: 100px;
  flex-shrink: 0;
}
.label-header,
.label-cell {
  display: flex;
  align-items: center;
  justify-content: center;
  border-right: 1px solid #e8e8e8;
  border-bottom: 1px solid #e8e8e8;
  font-size: 0.75rem;
  font-weight: 700;
}
.label-header {
  height: 128px;
  background-color: #f5f5f5;
  color: #111111;
}
.label-cell {
  height: 82px;
  color: #666666;
  background-color: #fafafa;
}
.label-cell.stripe {
  /* マージン率セル側の高さ（yg-margin-stripe）に合わせる */
  height: 118px;
  background-color: #f5f5f5;
}
.platform-columns-wrapper {
  display: flex;
  flex: 1;
  min-width: 0;
}
.platform-column {
  flex: 1;
  min-width: 0;
}
.chip-cell,
.grid-cell {
  border-right: 1px solid #e8e8e8;
  border-bottom: 1px solid #e8e8e8;
}
.chip-cell {
  height: 128px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 12px;
  box-sizing: border-box;
}
.platform-chip {
  width: 100%;
  height: 100%;
  border: 1px solid #e1e1e1;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  cursor: pointer;
  background: #ffffff;
  transition: all 0.25s ease;
  box-sizing: border-box;
}
.platform-chip:hover {
  border-color: #111111;
  transform: translateY(-2px);
}
.platform-chip.selected {
  background-color: #ffffff;
  color: #111111;
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15), 0 0 14px rgba(59, 130, 246, 0.35);
}
.platform-chip.is-coming-soon {
  cursor: not-allowed;
  opacity: 0.45;
  position: relative;
}
.platform-chip.is-coming-soon:hover {
  border-color: #e1e1e1;
  transform: none;
}
.coming-soon-badge {
  font-size: 0.6rem;
  font-weight: 600;
  color: #999999;
  letter-spacing: 0.05em;
}
.chip-logos {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  /* ロゴの実寸（アニメーションの余白・静止画のトリミング差）に関わらず、
     ロゴ枠の高さを揃えることで媒体名の縦位置を全媒体で統一する */
  min-height: 68px;
}
.lottie-wrapper {
  width: 44px;
  height: 44px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.lottie-player {
  width: 100%;
  height: 100%;
}
.static-logo-image {
  object-fit: contain;
}
/* Listingはアニメーション自体の見た目が他より小さいため、大きめの枠で表示 */
.lottie-wrapper.listing {
  width: 68px;
  height: 68px;
}
/* 静止画ロゴ（YG-Display&DGCのGoogle／LINEヤフー、LINE、X、DV360）は
   画像ごとの余白差を吸収するため高さ基準で表示し、横幅は画像のアスペクト比なりに可変させる */
.lottie-wrapper.line,
.lottie-wrapper.x,
.lottie-wrapper.dv360 {
  width: auto;
  height: 32px;
  overflow: visible;
}
/* YG-Display&DGCはロゴ2つを横並びで収める必要があるため、他より小さめの高さ基準にする */
.lottie-wrapper.yg-google,
.lottie-wrapper.yg-line-yahoo {
  width: auto;
  height: 20px;
  overflow: visible;
}
.lottie-wrapper.yg-google .static-logo-image,
.lottie-wrapper.yg-line-yahoo .static-logo-image,
.lottie-wrapper.line .static-logo-image,
.lottie-wrapper.x .static-logo-image,
.lottie-wrapper.dv360 .static-logo-image {
  width: auto;
  height: 100%;
}
.platform-chip-name {
  font-size: 0.75rem;
  font-weight: 400;
  color: #3a3a3a;
  letter-spacing: 0.08em;
}
.grid-cell {
  height: 82px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 10px;
  box-sizing: border-box;
}
.grid-cell.stripe {
  background-color: #fafafa;
}
.margin-select-line {
  width: 100%;
  border: none;
  background: transparent;
  outline: none;
  font-family: inherit;
  font-size: 0.8rem;
  color: #111111;
  text-align: center;
}
.margin-select-line:disabled,
.margin-input-line:disabled {
  color: #cccccc;
  cursor: not-allowed;
}
.margin-input-wrap-inline {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
}
.margin-input-line {
  width: 70px;
  border: none;
  border-bottom: 1px solid #d8d8d8;
  padding: 8px 4px;
  outline: none;
  text-align: right;
  background: transparent;
  font-family: inherit;
}
.margin-suffix-text {
  font-size: 0.8rem;
  margin-left: 4px;
  color: #666666;
}
/* --- 全媒体共通：マージン率の内訳・計算式表示 --- */
.grid-cell.stripe.yg-margin-stripe {
  /* イレギュラー時（内訳2行＋合計＋計算式）でもはみ出さない高さで固定し、
     通常時／Coming soon時も含めて全プラットフォームで行の高さを揃える */
  height: 118px;
  flex-direction: column;
  justify-content: center;
  gap: 6px;
  padding: 8px 6px;
  box-sizing: border-box;
}
.yg-margin-breakdown-line .margin-suffix-text {
  font-size: 0.62rem;
  margin-left: 1px;
  flex-shrink: 0;
}
.yg-margin-breakdown-line {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 3px;
  flex-wrap: nowrap;
  width: 100%;
}
.yg-margin-breakdown-branch {
  font-size: 0.62rem;
  color: #999999;
  line-height: 1;
}
.yg-margin-breakdown-label {
  font-size: 0.62rem;
  color: #666666;
  flex-shrink: 0;
}
.yg-margin-breakdown-input {
  width: 30px;
  font-size: 0.65rem;
  padding: 2px;
  text-align: right;
}
.yg-margin-rate-line {
  font-size: 0.85rem;
  font-weight: 700;
  color: #111111;
  text-align: center;
}
.yg-margin-formula-line {
  font-size: 0.65rem;
  font-weight: 400;
  color: #666666;
  text-align: center;
}
/* =========================
  6. STEP 3（YouTube タブ & マトリックス・テーブル）
========================= */
.youtube-form-container {
  width: 100%;
  min-width: 0;
  box-sizing: border-box;
}
.platform-tabs {
  position: relative;
  display: flex;
  gap: 48px;
  margin-bottom: 24px;
  padding-left: 8px;
  border-bottom: 1px solid #e2e2e2;
}
.material-tab {
  background: transparent;
  border: none;
  padding: 12px 2px;
  font-size: 0.92rem;
  font-weight: 300;
  letter-spacing: 0.04em;
  color: #9a9a9e;
  cursor: pointer;
  position: relative;
  transition: color 0.25s ease, transform 0.25s ease;
}
.material-tab:hover {
  color: #111111;
}
.material-tab.active {
  color: #3b82f6;
  font-weight: 500;
  transform: translateY(-1px);
}
/* YouTubeタブが選択されている時だけ文字色をYouTubeのテーマカラー（赤）に */
.material-tab.tab-btn-youtube.active {
  color: #ff0000;
}
/* 選択中のタブへスムーズに移動する下線インジケーター */
.tab-underline-indicator {
  position: absolute;
  bottom: -1px;
  height: 2px;
  border-radius: 2px;
  background: #3b82f6;
  transition: left 0.35s cubic-bezier(0.4, 0, 0.2, 1), width 0.35s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.25s ease;
}
.tab-underline-indicator.tab-underline-youtube {
  background: #ff0000;
}
.platform-content {
  width: 100%;
  min-width: 0;
  box-sizing: border-box;
}
.dark-card-wrapper {
  width: 100%;
  min-width: 0;
  overflow: hidden;
  border-radius: 18px;
  box-shadow: 0 10px 32px rgba(17,17,17,0.07), 0 1px 3px rgba(17,17,17,0.05);
  background: #ffffff;
  border: 1px solid #ebebeb;
  box-sizing: border-box;
}
.listing-info-label {
  color: #111111;
  display: block;
  margin-bottom: 6px;
}
.sim-form-container.dark-mode .listing-info-label {
  color: #f2f2f4;
}
.table-scroll-container {
  position: relative;
  width: 100%;
  overflow-x: auto;
  overflow-y: hidden;
  -webkit-overflow-scrolling: touch;
  box-sizing: border-box;
  scrollbar-width: thin;
  scrollbar-color: #5a5a5f #eef1f6;
}
/* --- 横スクロールバーのカスタムデザイン --- */
.table-scroll-container::-webkit-scrollbar {
  height: 9px;
}
.table-scroll-container::-webkit-scrollbar-track {
  background: #eef1f6;
  border-radius: 999px;
}
.table-scroll-container::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, #6b6b70 0%, #3a3a3e 100%);
  border-radius: 999px;
  border: 2px solid #eef1f6;
  background-clip: padding-box;
}
.table-scroll-container::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(180deg, #808086 0%, #4a4a4f 100%);
  background-clip: padding-box;
}
.pattern-matrix-table {
  width: 100% !important;
  table-layout: fixed !important;
  border-collapse: collapse;
  background: #ffffff;
}
.pattern-matrix-table th {
  height: 38px;
  padding: 4px 10px;
  background: linear-gradient(180deg, #222222 0%, #111111 100%);
  color: #ffffff;
  border-bottom: 1px solid #111111;
  border-right: none !important;
  font-size: 11px;
  font-weight: 700;
  white-space: nowrap;
  text-align: left;
  box-sizing: border-box;
}
.pattern-matrix-table td {
  position: relative;
  height: 88px;
  padding: 14px 10px !important;
  vertical-align: middle;
  text-align: left;
  border-bottom: 1px solid #ececec;
  border-right: none !important;
  background: #ffffff;
  font-size: 12px;
  box-sizing: border-box;
  overflow: hidden;
}
.pattern-matrix-table tbody tr:hover td {
  background: #f8f9fa;
}
.pattern-matrix-table td.text-center { text-align: center; }
.font-bold { font-weight: 700; }
.pattern-matrix-table td.text-right { text-align: right; }
/* --- 概要セル（クリックでEditモーダルを開く） --- */
.overview-cell {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  cursor: pointer;
  color: #111111;
}
.overview-cell.is-empty {
  color: #b0b0b5;
}
.tag-cell.is-empty {
  color: #b0b0b5;
}
.tag-cell-extra {
  font-size: 11px;
  color: #8e8e93;
}
/* --- Basic列（操作/#）を固定表示 --- */
.pattern-matrix-table tbody td:nth-child(1) {
  position: sticky;
  left: 0;
  z-index: 2;
}
.pattern-matrix-table tbody td:nth-child(2) {
  position: sticky;
  left: 48px;
  z-index: 2;
}
.pattern-matrix-table thead th:nth-child(1) {
  position: sticky;
  left: 0;
  z-index: 3;
}
.pattern-matrix-table thead th:nth-child(2) {
  position: sticky;
  left: 48px;
  z-index: 3;
}
.pattern-matrix-table thead th {
  background-color: #111111;
}
.pattern-matrix-table tbody td:nth-child(1),
.pattern-matrix-table tbody td:nth-child(2) {
  background-color: #ffffff;
}
.pattern-matrix-table tbody tr:nth-child(even) td:nth-child(1),
.pattern-matrix-table tbody tr:nth-child(even) td:nth-child(2) {
  background-color: #fafbfc;
}
/* ボディ・ヘッダーとも縦罫線を持たず、横線のみで行を区切るクリーンな表組みにする */
/* --- ゼブラストライプ --- */
.pattern-matrix-table tbody tr:nth-child(even) td {
  background: #fafbfc;
}
.pattern-matrix-table tbody tr:nth-child(even):hover td {
  background: #f4f5f6;
}
/* --- 編集モーダルのフォーム部品 --- */
.matrix-select {
  appearance: auto;
}
.matrix-input:focus,
.matrix-select:focus {
  border-color: #3b82f6 !important;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
  outline: none;
}
.matrix-input:disabled,
.matrix-select:disabled {
  background: #f2f2f7;
  color: rgba(0, 0, 0, 0.3);
  -webkit-text-fill-color: rgba(0, 0, 0, 0.3) !important;
  cursor: not-allowed;
}
.matrix-input::placeholder,
.transparent-input::placeholder {
  color: #b0b0b5;
  -webkit-text-fill-color: #b0b0b5 !important;
}
.matrix-select.is-placeholder {
  color: #b0b0b5;
  -webkit-text-fill-color: #b0b0b5 !important;
}
.matrix-select.is-placeholder:disabled {
  color: rgba(0, 0, 0, 0.3);
  -webkit-text-fill-color: rgba(0, 0, 0, 0.3) !important;
}
/* ドロップダウンを開いた時、実際の選択肢（プレースホルダー以外）は常に通常の文字色にする */
.matrix-select option:not([value=""]) {
  color: #1d1d1f;
}
/* --- 操作列（削除・複製・編集）：セル全体をボタン化 --- */
.pattern-matrix-table td.row-action-cell {
  padding: 0 !important;
  border-right: none !important;
  height: 100%;
}
.row-action-stack {
  display: flex;
  flex-direction: column;
  height: 100%;
  min-height: 40px;
}
.row-action-btn {
  flex: 1;
  width: 100%;
  min-height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  background: transparent;
  padding: 0;
  margin: 0;
  color: #666666;
  cursor: pointer;
  transition: background-color 0.15s ease, color 0.15s ease;
}
.row-action-btn svg {
  width: 18px;
  height: 18px;
}
.row-action-delete:hover:not(:disabled) {
  background-color: rgba(224, 49, 49, 0.1);
  color: #e03131;
}
.row-action-copy:hover {
  background-color: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
}
.row-action-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
  background-color: transparent !important;
}
.period-wrap {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 2px;
}
.add-pattern-area {
  display: flex;
  justify-content: center;
  padding: 12px;
  background: #f7f7f8;
  border-top: 1px solid #e2e2e2;
}
.add-new-btn {
  border: 1px solid #d7d7d7;
  background: #ffffff;
  border-radius: 999px;
  padding: 8px 20px;
  font-family: inherit;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}
.add-new-btn:hover {
  background: #111111;
  color: #ffffff;
  border-color: #111111;
}
/* =========================
  7. モーダル
========================= */
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 1000;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  box-sizing: border-box;
}
.modal-content {
  width: 100%;
  max-width: 760px;
  max-height: 85vh;
  overflow-y: auto;
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
  padding: 24px;
  box-sizing: border-box;
}
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 18px;
}
.modal-header h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 700;
}
.btn-close {
  width: 34px;
  height: 34px;
  border: none;
  background: #f3f3f3;
  border-radius: 50%;
  font-size: 22px;
  line-height: 1;
  cursor: pointer;
}
.modal-toolbar {
  display: flex;
  gap: 8px;
  margin-bottom: 14px;
}
.modal-toolbar input {
  flex: 1;
  min-width: 0;
  padding: 10px 12px;
  border: 1px solid rgba(0, 0, 0, 0.15);
  border-radius: 10px;
  outline: none;
  font-family: inherit;
}
.modal-toolbar button {
  border: 1px solid rgba(0, 0, 0, 0.12);
  background: #ffffff;
  border-radius: 10px;
  padding: 8px 14px;
  cursor: pointer;
  font-family: inherit;
}
.modal-selected-header,
.modal-options-header {
  font-size: 12px;
  font-weight: 700;
  color: #666666;
  margin-bottom: 8px;
}
.modal-selected-body {
  min-height: 48px;
  padding: 10px;
  background: #f7f7f8;
  border-radius: 10px;
  margin-bottom: 18px;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px;
}
.selected-chip {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: rgba(0, 100, 220, 0.08);
  color: rgb(0, 100, 220);
  border-radius: 999px;
  padding: 4px 8px;
  font-size: 12px;
}
.selected-chip button {
  border: 0;
  background: transparent;
  color: inherit;
  cursor: pointer;
  padding: 0;
  font-size: 14px;
}
.empty-selected {
  color: #8e8e93;
  font-size: 12px;
}
.pref-grid,
.target-option-list {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 6px;
  margin-bottom: 20px;
}
.target-option-list {
  display: flex;
  flex-direction: column;
  gap: 4px;
  max-height: 300px;
  overflow-y: auto;
}
.pref-option,
.target-option {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 40px;
  padding: 8px 10px;
  border: 1px solid #e2e2e6;
  background: #ffffff;
  border-radius: 8px;
  cursor: pointer;
  font-size: 12px;
  font-family: inherit;
  transition: all 0.15s ease;
}
.pref-option:hover,
.target-option:hover {
  background: #f2f2f7;
}
.pref-option.selected,
.target-option.selected {
  background: rgba(0, 100, 220, 0.08);
  color: rgb(0, 100, 220);
  border-color: rgb(0, 100, 220);
  font-weight: 700;
}
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}
.modal-footer button {
  border: 1px solid rgba(0, 0, 0, 0.12);
  background: #ffffff;
  border-radius: 10px;
  padding: 10px 18px;
  cursor: pointer;
  font-family: inherit;
}
.modal-confirm-btn {
  background: rgb(0, 100, 220) !important;
  color: #ffffff !important;
  border-color: rgb(0, 100, 220) !important;
  font-weight: 700;
}
/* =========================
  7.5 Notion風カラータグ & 選択肢トグル
========================= */
.notion-tag {
  display: inline-flex;
  align-items: center;
  padding: 2px 8px;
  border-radius: 5px;
  font-size: 11px;
  font-weight: 600;
  line-height: 1.6;
  white-space: nowrap;
}
.notion-tag-gray { background: #f1f1ef; color: #6b6b6b; }
.notion-tag-pink { background: #fae0eb; color: #b8477a; }
.notion-tag-orange { background: #fbecdd; color: #d9730d; }
.notion-tag-red { background: #fbe4e4; color: #c4554d; }
.notion-tag-purple { background: #eee0f8; color: #9065b0; }
.notion-tag-blue { background: #ddebf1; color: #337ea9; }
.notion-tag-green { background: #dbeddb; color: #448361; }
/* --- テーブル上のタグセル --- */
.tag-cell {
  cursor: pointer;
  white-space: normal;
}
.tag-cell-inner {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  align-items: center;
  justify-content: flex-start;
}
/* --- 編集モーダルのNotion風トグルドロップダウン --- */
.notion-select-wrap {
  position: relative;
  margin-top: 5px;
}
.notion-select-trigger {
  width: 100%;
  min-height: 40px;
  height: auto;
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 6px 10px;
  text-align: left;
  cursor: pointer;
  background: #ffffff;
  transition: border-color 0.15s ease;
}
.notion-select-trigger:hover {
  border-color: #b8b8b8;
}
.notion-select-trigger:disabled {
  background: #f2f2f7;
  color: rgba(0, 0, 0, 0.3);
  cursor: not-allowed;
}
.notion-select-trigger:disabled:hover {
  border-color: #ddd;
}
.notion-select-trigger-multi {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 4px;
}
.notion-select-placeholder {
  color: #9a9a9a;
  font-size: 0.9rem;
}
.notion-select-panel {
  position: absolute;
  top: calc(100% + 6px);
  left: 0;
  right: 0;
  z-index: 20;
  background: #ffffff;
  border: 1px solid #e6e6e6;
  border-radius: 8px;
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
  padding: 6px;
  max-height: 260px;
  overflow-y: auto;
}
.notion-option-row {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding: 8px 8px;
  border: none;
  background: transparent;
  border-radius: 6px;
  cursor: pointer;
  font-family: inherit;
  text-align: left;
}
.notion-option-row:hover {
  background: #f5f5f5;
}
.notion-check {
  width: 16px;
  height: 16px;
  color: #3b82f6;
  flex-shrink: 0;
}
.notion-panel-empty {
  margin: 0;
  padding: 10px 8px;
  font-size: 0.8rem;
  color: #9a9a9a;
}
/* =========================
  8. アクション・ボタン
========================= */
.action-area {
  margin-top: 48px;
  display: flex;
  justify-content: flex-end;
}
.next-btn,
.back-btn {
  border: none;
  cursor: pointer;
  font-family: inherit;
}
.next-btn {
  display: inline-flex;
  align-items: center;
  gap: 16px;
  background-color: #111111;
  color: #ffffff;
  padding: 14px 22px;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 700;
  transition: all 0.25s ease;
}
.next-btn:disabled {
  opacity: 0.25;
  cursor: not-allowed;
}
.next-btn:not(:disabled):hover {
  transform: translateX(4px);
}
.back-btn {
  background: transparent;
  color: #666666;
  padding: 14px 18px;
  font-size: 0.85rem;
  font-weight: 600;
  border-radius: 8px;
}
.back-btn:hover {
  background-color: #f5f5f5;
}
.step2-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 48px;
}
.animated-btn {
  background-color: #111111;
  color: #ffffff;
  border: 1px solid #111111;
  padding: 16px 56px;
  font-size: 0.95rem;
  font-weight: 600;
  letter-spacing: 0.06em;
  border-radius: 6px;
  cursor: pointer;
  opacity: 0;
  transform: translateY(12px);
  pointer-events: none;
  transition:
    opacity 0.4s ease,
    transform 0.4s ease,
    background-color 0.2s ease;
}
.animated-btn.visible {
  opacity: 1;
  transform: translateY(0);
  pointer-events: auto;
}
.btn-arrow {
  display: inline-block;
  font-size: 1.1rem;
  transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.animated-btn:hover:not(:disabled) .btn-arrow {
  transform: translateX(6px);
}
.btn-submit-icon {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
  transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.animated-btn:hover:not(:disabled) .btn-submit-icon {
  transform: translate(3px, -3px);
}
.animated-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}
/* =========================
  9. アニメーション & レスポンシブ
========================= */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.35s ease;
}
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(10px);
}
.tab-content-fade-enter-active,
.tab-content-fade-leave-active {
  transition: opacity 0.25s ease, transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.tab-content-fade-enter-from {
  opacity: 0;
  transform: translateY(8px) scale(0.99);
}
.tab-content-fade-leave-to {
  opacity: 0;
  transform: translateY(-6px) scale(0.99);
}
@media (max-width: 768px) {
  .header {
    padding: 0 16px;
  }
  .company-name {
    font-size: 0.85rem;
  }
  .app-title {
    display: none;
  }
  .step {
    padding: 0 12px;
    font-size: 0.68rem;
  }
  .content-wrapper {
    padding: 12px 4px 30px 4px !important;
  }
  .form-body-vertical {
    gap: 24px;
    margin-top: 24px;
  }
  .title-area h2,
  .platform-header h2 {
    font-size: 1.75rem;
  }
  .animated-btn {
    width: 100%;
    padding: 16px 0;
  }
  .step2-actions {
    flex-direction: column-reverse;
    gap: 16px;
  }
  .back-btn {
    width: 100%;
  }
  .platform-grid-container {
    overflow-x: auto;
  }
  .platform-label-column {
    min-width: 100px;
  }
  .platform-columns-wrapper {
    min-width: 720px;
  }
  .modal-overlay {
    padding: 12px;
  }
  .modal-content {
    max-height: 90vh;
    padding: 18px;
  }
  .pref-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  .table-scroll-container {
    overflow-x: auto;
  }
}
/* =========================
  10. ダークモード
========================= */
.sim-form-container.dark-mode {
  background-color: #0d0d0f;
  color: #f2f2f4;
  transition: background-color 0.3s ease, color 0.3s ease;
}
.sim-form-container.dark-mode .header {
  background-color: rgba(13, 13, 15, 0.9);
  border-bottom-color: rgba(255, 255, 255, 0.08);
}
.sim-form-container.dark-mode .company-name {
  color: #f2f2f4;
}
.sim-form-container.dark-mode .app-title {
  color: #7a7a7e;
}
.sim-form-container.dark-mode .step {
  color: #6a6a6e;
}
.sim-form-container.dark-mode .step.active {
  background-color: #f2f2f4;
  color: #0d0d0f;
}
/* ENTRY */
.sim-form-container.dark-mode .title-area h2,
.sim-form-container.dark-mode .platform-header h2 {
  color: #f2f2f4;
}
.sim-form-container.dark-mode .required-label,
.sim-form-container.dark-mode .sub-label {
  color: #7a7a7e;
}
.sim-form-container.dark-mode .num {
  background-color: #f2f2f4;
  color: #0d0d0f;
}
.sim-form-container.dark-mode .form-group label {
  color: #f2f2f4;
}
.sim-form-container.dark-mode .form-group input[type="text"],
.sim-form-container.dark-mode .form-group input[type="email"],
.sim-form-container.dark-mode .form-group input[type="password"],
.sim-form-container.dark-mode .custom-date-input,
.sim-form-container.dark-mode .custom-textarea {
  color: #f2f2f4;
  border-bottom-color: rgba(255, 255, 255, 0.16);
}
.sim-form-container.dark-mode .custom-textarea:focus,
.sim-form-container.dark-mode .form-group input[type="text"]:focus,
.sim-form-container.dark-mode .form-group input[type="email"]:focus,
.sim-form-container.dark-mode .form-group input[type="password"]:focus,
.sim-form-container.dark-mode .custom-date-input:focus {
  border-bottom-color: #f2f2f4;
}
.sim-form-container.dark-mode .custom-radio {
  border-color: #6a6a6e;
}
.sim-form-container.dark-mode .radio-item:hover .custom-radio,
.sim-form-container.dark-mode .radio-item.is-selected .custom-radio {
  border-color: #f2f2f4;
}
.sim-form-container.dark-mode .custom-radio::after {
  background-color: #f2f2f4;
}
.sim-form-container.dark-mode .radio-label-text {
  color: #d8d8dc;
}
/* PLATFORMS */
.sim-form-container.dark-mode .label-header {
  background-color: #1a1a1d;
  color: #d8d8dc;
}
.sim-form-container.dark-mode .label-cell {
  background-color: #17171a;
  color: #8a8a8e;
}
.sim-form-container.dark-mode .label-cell.stripe {
  background-color: #1a1a1d;
}
.sim-form-container.dark-mode .platform-grid-container {
  border-color: rgba(255, 255, 255, 0.08);
}
.sim-form-container.dark-mode .chip-cell,
.sim-form-container.dark-mode .grid-cell {
  border-color: rgba(255, 255, 255, 0.08);
}
.sim-form-container.dark-mode .grid-cell.stripe {
  background-color: #17171a;
}
.sim-form-container.dark-mode .platform-chip {
  background: #1a1a1d;
  border-color: rgba(255, 255, 255, 0.12);
  color: #f2f2f4;
}
.sim-form-container.dark-mode .platform-chip.selected {
  background-color: #1a1a1d;
  border-color: #3b82f6;
}
.sim-form-container.dark-mode .margin-select-line,
.sim-form-container.dark-mode .margin-input-line {
  color: #f2f2f4;
}
.sim-form-container.dark-mode .margin-input-line {
  border-bottom-color: rgba(255, 255, 255, 0.16);
}
.sim-form-container.dark-mode .yg-margin-breakdown-label,
.sim-form-container.dark-mode .yg-margin-rate-line {
  color: #8a8a8e;
}
.sim-form-container.dark-mode .yg-margin-formula-line {
  color: #f2f2f4;
}
/* TABLE FORM: タブ */
.sim-form-container.dark-mode .platform-tabs {
  border-bottom-color: rgba(255, 255, 255, 0.1);
}
.sim-form-container.dark-mode .material-tab {
  color: #6a6a6e;
}
.sim-form-container.dark-mode .material-tab:hover {
  color: #f2f2f4;
}
/* TABLE FORM: テーブル */
.sim-form-container.dark-mode .dark-card-wrapper {
  background: #17171a;
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow: 0 10px 32px rgba(0, 0, 0, 0.4);
}
.sim-form-container.dark-mode .pattern-matrix-table {
  background: #17171a;
}
.sim-form-container.dark-mode .pattern-matrix-table th {
  background: linear-gradient(180deg, #1c1c1f 0%, #0d0d0f 100%);
  border-bottom-color: #000000;
}
.sim-form-container.dark-mode .pattern-matrix-table td {
  background: #17171a;
  color: #f2f2f4;
  border-bottom-color: rgba(255, 255, 255, 0.06);
}
.sim-form-container.dark-mode .pattern-matrix-table tbody tr:nth-child(even) td {
  background: #1c1c1f;
}
.sim-form-container.dark-mode .pattern-matrix-table tbody tr:hover td {
  background: #232327;
}
.sim-form-container.dark-mode .pattern-matrix-table tbody td:nth-child(1),
.sim-form-container.dark-mode .pattern-matrix-table tbody td:nth-child(2) {
  background: #17171a;
}
.sim-form-container.dark-mode .pattern-matrix-table tbody tr:nth-child(even) td:nth-child(1),
.sim-form-container.dark-mode .pattern-matrix-table tbody tr:nth-child(even) td:nth-child(2) {
  background: #1c1c1f;
}
.sim-form-container.dark-mode .overview-cell {
  color: #f2f2f4;
}
.sim-form-container.dark-mode .overview-cell.is-empty,
.sim-form-container.dark-mode .tag-cell.is-empty,
.sim-form-container.dark-mode .tag-cell-extra {
  color: #6a6a6e;
}
.sim-form-container.dark-mode .notion-tag-gray {
  background: rgba(255, 255, 255, 0.1);
  color: #e0e0e4;
}
.sim-form-container.dark-mode .row-action-btn {
  color: #8a8a8e;
}
.sim-form-container.dark-mode .add-pattern-area {
  background: #1c1c1f;
  border-top-color: rgba(255, 255, 255, 0.08);
}
.sim-form-container.dark-mode .add-new-btn {
  background: #17171a;
  border-color: rgba(255, 255, 255, 0.15);
  color: #f2f2f4;
}
.sim-form-container.dark-mode .add-new-btn:hover {
  background: #f2f2f4;
  color: #0d0d0f;
  border-color: #f2f2f4;
}
.sim-form-container.dark-mode .table-scroll-container {
  scrollbar-color: #5a5a5f #17171a;
}
.sim-form-container.dark-mode .table-scroll-container::-webkit-scrollbar-track {
  background: #17171a;
}
.sim-form-container.dark-mode .table-scroll-container::-webkit-scrollbar-thumb {
  border-color: #17171a;
}
/* ボタン類 */
.sim-form-container.dark-mode .back-btn {
  color: #9a9a9e;
}
.sim-form-container.dark-mode .back-btn:hover {
  background-color: rgba(255, 255, 255, 0.06);
}
.sim-form-container.dark-mode .animated-btn {
  background-color: #f2f2f4;
  color: #0d0d0f;
  border-color: #f2f2f4;
}
/* 編集モーダル */
.sim-form-container.dark-mode .modal-overlay {
  background: rgba(0, 0, 0, 0.65);
}
.sim-form-container.dark-mode .modal-content {
  background: #17171a;
  color: #f2f2f4;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}
.sim-form-container.dark-mode .modal-header {
  border-bottom-color: rgba(255, 255, 255, 0.08);
}
.sim-form-container.dark-mode .modal-header h3 {
  color: #f2f2f4;
}
.sim-form-container.dark-mode .btn-close {
  background: rgba(255, 255, 255, 0.08);
  color: #d8d8dc;
}
.sim-form-container.dark-mode .matrix-input,
.sim-form-container.dark-mode .matrix-select,
.sim-form-container.dark-mode .notion-select-trigger {
  background: #0d0d0f;
  border-color: rgba(255, 255, 255, 0.15);
  color: #f2f2f4;
}
.sim-form-container.dark-mode .matrix-select option {
  background: #17171a;
  color: #f2f2f4;
}
.sim-form-container.dark-mode .matrix-input:disabled,
.sim-form-container.dark-mode .matrix-select:disabled,
.sim-form-container.dark-mode .notion-select-trigger:disabled {
  background: #131315;
  color: rgba(255, 255, 255, 0.25);
  -webkit-text-fill-color: rgba(255, 255, 255, 0.25) !important;
}
.sim-form-container.dark-mode .notion-select-panel {
  background: #1c1c1f;
  border-color: rgba(255, 255, 255, 0.1);
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.5);
}
.sim-form-container.dark-mode .notion-option-row:hover {
  background: rgba(255, 255, 255, 0.06);
}
.sim-form-container.dark-mode .modal-toolbar input {
  background: #0d0d0f;
  border-color: rgba(255, 255, 255, 0.15);
  color: #f2f2f4;
}
.sim-form-container.dark-mode .modal-toolbar button,
.sim-form-container.dark-mode .modal-footer button {
  background: #0d0d0f;
  border-color: rgba(255, 255, 255, 0.15);
  color: #f2f2f4;
}
.sim-form-container.dark-mode .modal-selected-header,
.sim-form-container.dark-mode .modal-options-header,
.sim-form-container.dark-mode .modal-candidates-header {
  color: #8a8a8e;
}
.sim-form-container.dark-mode .modal-selected-body {
  background: #0d0d0f;
}
.sim-form-container.dark-mode .empty-selected {
  color: #6a6a6e;
}
.sim-form-container.dark-mode .pref-option,
.sim-form-container.dark-mode .target-option {
  background: #0d0d0f;
  border-color: rgba(255, 255, 255, 0.1);
  color: #f2f2f4;
}
.sim-form-container.dark-mode .pref-option:hover,
.sim-form-container.dark-mode .target-option:hover {
  background: rgba(255, 255, 255, 0.06);
}
</style>