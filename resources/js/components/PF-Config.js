export const YOUTUBE_CONFIG = {
  id: 'youtube',
  name: 'YouTube',
  videoLengthOptions: [
    { value: "なし", text: "なし" },
    { value: "6s＊バンパー", text: "6s＊バンパー" },
    { value: "15s", text: "15s" },
    { value: "30s", text: "30s" },
    { value: "45s", text: "45s" },
    { value: "60s", text: "60s" },
    { value: "90s", text: "90s" },
    { value: "120s", text: "120s" }
  ],
  restrictedVideoLengthOptions: [
    { value: "なし", text: "なし" },
    { value: "15s", text: "15s" },
    { value: "30s ※CTVのみ", text: "30s ※CTVのみ" },
    { value: "6sバンパー+15s ※β版", text: "6sバンパー+15s ※β版" }
  ],
  prefectureList: [
    "★ALL","北海道","青森県","岩手県","宮城県","秋田県","山形県","福島県",
    "茨城県","栃木県","群馬県","埼玉県","千葉県","東京都","神奈川県",
    "新潟県","富山県","石川県","福井県","山梨県","長野県","岐阜県","静岡県","愛知県","三重県",
    "滋賀県","京都府","大阪府","兵庫県","奈良県","和歌山県",
    "鳥取県","島根県","岡山県","広島県","山口県",
    "徳島県","香川県","愛媛県","高知県",
    "福岡県","佐賀県","長崎県","熊本県","大分県","宮崎県","鹿児島県","沖縄県"
  ],
  genderOptions: ['ALL', '男性', '女性'],
  ageOptions: ['ALL', '18-24', '25-34', '35-44', '45-54', '55-64', '65+'],
  deviceOptions: ['ALL', 'Mobile/PC', 'Mobile', 'PC', 'TV'],
  
  // ターゲティングカテゴリ定義
  targetCategories: {
    affinity: [
      "スポーツ、フィットネス", "スポーツ、フィットネス>スポーツファン", "スポーツ、フィットネス>健康、フィットネス マニア",
      "テクノロジー", "テクノロジー>ソーシャル メディア ファン", "テクノロジー>ハイテク好き",
      "ニュース、政治", "フード、ダイニング", "メディア、エンターテイメント", "ライフスタイル、趣味", "旅行"
    ],
    inmarket: [
      "アート、工芸用品", "アパレル、アクセサリ", "イベントのチケット", "ギフト、行事",
      "コンピュータ、周辺機器", "スポーツ、フィットネス", "ソフトウェア"
    ],
    detailedDemo: [
      "教育>現役の大学生", "教育>最終学歴>学士号", "教育>最終学歴>高校卒",
      "子供の有無>子供あり", "就業状況>業種>サービス業", "住宅所有状況>住宅所有", "配偶者の有無>既婚"
    ],
    lifeEvent: [
      "マイホームの購入", "引越し", "起業", "結婚", "自宅のリフォーム", "新しいペット", "転職"
    ]
  },

  defaultRow: {
    menu: 'バンパー広告',
    placement: '',
    verticalAspect: 'なし',
    horizontalAspect: 'なし',
    periodNum: 1,
    periodUnit: 'ヶ月',
    budget: '',
    prefArea: [], // 配列で保持（★ALLなど）
    cityArea: '',
    gender: 'ALL',
    age1: 'ALL',
    age2: '',
    age3: '',
    target1: { category: '', selected: [] },
    target2: { category: '', selected: [] },
    target3: { category: '', selected: [] },
    device: 'ALL',
    notes: ''
  }
};