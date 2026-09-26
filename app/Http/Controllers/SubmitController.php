<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use App\Exports\EstimateWorkbookExport;
use App\Exports\PlatformPatternSheet;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Maatwebsite\Excel\Excel as ExcelFormat;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class SubmitController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->json()->all();
        $entry = $data['entry'];

        $entryId = DB::table('entries')->insertGetId([
            'applicant_name' => $entry['applicantName'],
            'department'     => $entry['department'],
            'email'          => $entry['email'],
            'client_name'    => $entry['clientName'],
            'project_name'   => $entry['projectName'],
            'due_date'       => $entry['dueDate'],
            'remarks'        => $entry['remarks'],
        ]);

        $platformLabels = [
            'meta'    => 'Meta',
            'youtube' => 'YouTube',
            'yg'      => 'YG-Display&DGC',
            'listing' => 'リスティング',
            'x'       => 'X',
            'line'    => 'LINE',
        ];

        $departmentSlugMap = [
            'QM-SEM-2ナビ' => 'qm-sem-2',
            'QM-SEM-1ナビ' => 'qm-sem-1',
            'QM-SNSナビ'   => 'qm-sns',
        ];

        // 通知先ごと（webhook単位）に、届いた媒体名をまとめておく箱
        $notifications = [];

        foreach ($data['platforms'] as $platformKey => $platformData) {
            if (!$platformData['selected']) {
                continue;
            }

            // 振り分け先を先に調べる（campaigns保存時に使うため）
            $routing = DB::table('department_routing')
                ->where('platform', $platformKey)
                ->where(function ($query) use ($entry) {
                    $query->where('applicant_department', $entry['department'])
                          ->orWhereNull('applicant_department');
                })
                ->orderByRaw('applicant_department IS NULL')
                ->first();

            $campaignId = DB::table('campaigns')->insertGetId([
                'entry_id'             => $entryId,
                'platform'             => $platformKey,
                'client_name'          => $entry['clientName'],
                'project_name'         => $entry['projectName'],
                'applicant_name'       => $entry['applicantName'],
                'margin_type'          => $platformData['marginType'],
                'margin_value'         => $platformData['marginValue'],
                'receiving_department' => $routing->receiving_department ?? null,
                'status'               => 'Assign待ち',
            ]);

            // Slack通知は、ここでは「送らず」に情報だけ集めておく
            if ($routing && $routing->webhook_url) {
                $key = $routing->webhook_url;
                if (!isset($notifications[$key])) {
                    $notifications[$key] = [
                        'routing'   => $routing,
                        'platforms' => [],
                    ];
                }
                $notifications[$key]['platforms'][] = $platformLabels[$platformKey] ?? $platformKey;
            }

            if ($platformKey === 'youtube') {
                foreach ($data['youtube']['patterns'] as $pattern) {
                    DB::table('youtube_patterns')->insert([
                        'campaign_id'         => $campaignId,
                        'menu'                => $pattern['menu'],
                        'placement'           => json_encode($pattern['placement'], JSON_UNESCAPED_UNICODE),
                        'vertical_creative'   => $pattern['verticalCreative'],
                        'horizontal_creative' => $pattern['horizontalCreative'],
                        'period_number'       => $pattern['periodNumber'],
                        'period_unit'         => $pattern['periodUnit'],
                        'budget'              => $pattern['budget'],
                        'pref_names'          => json_encode($pattern['prefNames'], JSON_UNESCAPED_UNICODE),
                        'city'                => $pattern['city'],
                        'gender'              => json_encode($pattern['gender'], JSON_UNESCAPED_UNICODE),
                        'age1'                => $pattern['age1'],
                        'age2'                => $pattern['age2'],
                        'age3'                => $pattern['age3'],
                        // targeting1〜3は新形式（SimForm.vueの3枠対応版）が前提だが、
                        // 旧形式（targetingType単数）のまま送信された場合はtargeting1にフォールバックする
                        'targeting1_type'      => $pattern['targeting1Type'] ?? ($pattern['targetingType'] ?? null),
                        // SimForm.vue のYouTube用フォームは単一枠のときフィールド名が「target」になる
                        // （旧来は「targetValues」を見ていたため、選択したカテゴリの詳細値が保存されていなかった）
                        'targeting1_values'    => json_encode($pattern['targeting1Values'] ?? ($pattern['target'] ?? ($pattern['targetValues'] ?? [])), JSON_UNESCAPED_UNICODE),
                        'targeting2_type'      => $pattern['targeting2Type'] ?? null,
                        'targeting2_values'    => json_encode($pattern['targeting2Values'] ?? [], JSON_UNESCAPED_UNICODE),
                        'targeting3_type'      => $pattern['targeting3Type'] ?? null,
                        'targeting3_values'    => json_encode($pattern['targeting3Values'] ?? [], JSON_UNESCAPED_UNICODE),
                        'device'              => json_encode($pattern['device'], JSON_UNESCAPED_UNICODE),
                        'notes'               => $pattern['notes'],
                    ]);
                }
            }

            if ($platformKey === 'meta') {
                foreach ($data['meta']['patterns'] as $pattern) {
                    DB::table('meta_patterns')->insert([
                        'campaign_id'        => $campaignId,
                        'campaign_objective' => $pattern['campaignObjective'],
                        'kpi'                => $pattern['kpi'],
                        'menu'               => $pattern['menu'],
                        'placement'          => json_encode($pattern['placement'], JSON_UNESCAPED_UNICODE),
                        'billing'            => $pattern['billing'],
                        'period_number'      => $pattern['periodNumber'],
                        'period_unit'        => $pattern['periodUnit'],
                        'period_timing'      => $pattern['periodTiming'],
                        'budget'             => $pattern['budget'],
                        'age1'               => $pattern['age1'],
                        'age2'               => $pattern['age2'],
                        'gender'             => $pattern['gender'],
                        'device'             => $pattern['device'],
                        'pref_names'         => json_encode($pattern['prefNames'], JSON_UNESCAPED_UNICODE),
                        'city'               => $pattern['city'],
                        'fq_type'            => $pattern['fqType'],
                        'fq_num'             => $pattern['fqNum'],
                        'fq_days'            => $pattern['fqDays'],
                        'interest'           => $pattern['interest'],
                        'remarks'            => $pattern['remarks'],
                    ]);
                }
            }

            if ($platformKey === 'yg') {
                foreach ($data['yg']['patterns'] as $pattern) {
                    DB::table('yg_patterns')->insert([
                        'campaign_id'        => $campaignId,
                        'menu'               => $pattern['menu'],
                        'video_duration'     => $pattern['videoDuration'],
                        'billing'            => $pattern['billing'],
                        'period_number'      => $pattern['periodNumber'],
                        'period_unit'        => $pattern['periodUnit'],
                        'budget'             => $pattern['budget'],
                        'pref_names'         => json_encode($pattern['prefNames'], JSON_UNESCAPED_UNICODE),
                        'city'               => $pattern['city'],
                        'gender'             => $pattern['gender'],
                        'age1'               => $pattern['age1'],
                        'age2'               => $pattern['age2'],
                        'age3'               => $pattern['age3'],
                        // targeting1〜3は新形式（SimForm.vueの3枠対応版）が前提だが、
                        // 旧形式（targetingType単数）のまま送信された場合はtargeting1にフォールバックする
                        'targeting1_type'    => $pattern['targeting1Type'] ?? ($pattern['targetingType'] ?? null),
                        'targeting1_values'  => json_encode($pattern['targeting1Values'] ?? ($pattern['target'] ?? ($pattern['targetValues'] ?? [])), JSON_UNESCAPED_UNICODE),
                        'targeting2_type'    => $pattern['targeting2Type'] ?? null,
                        'targeting2_values'  => json_encode($pattern['targeting2Values'] ?? [], JSON_UNESCAPED_UNICODE),
                        'targeting3_type'    => $pattern['targeting3Type'] ?? null,
                        'targeting3_values'  => json_encode($pattern['targeting3Values'] ?? [], JSON_UNESCAPED_UNICODE),
                        'device'             => json_encode($pattern['device'], JSON_UNESCAPED_UNICODE),
                        'remarks'            => $pattern['remarks'],
                    ]);
                }
            }

            if ($platformKey === 'listing') {
                // 依頼種別・LP・KW（部分一致/フレーズ一致/完全一致）は案件（campaign）単位で1回だけ入力される情報
                DB::table('campaigns')->where('id', $campaignId)->update([
                    'listing_request_types' => json_encode($data['listing']['requestTypes'] ?? [], JSON_UNESCAPED_UNICODE),
                    'listing_lp'            => $data['listing']['lp'] ?? null,
                    'listing_kw_broad'      => $data['listing']['kwBroad'] ?? null,
                    'listing_kw_phrase'     => $data['listing']['kwPhrase'] ?? null,
                    'listing_kw_exact'      => $data['listing']['kwExact'] ?? null,
                ]);
                foreach ($data['listing']['patterns'] as $pattern) {
                    DB::table('listing_patterns')->insert([
                        'campaign_id'   => $campaignId,
                        'menu'          => $pattern['menu'],
                        'period_number' => $pattern['periodNumber'],
                        'period_unit'   => $pattern['periodUnit'],
                        'budget'        => $pattern['budget'],
                        'pref_names'    => json_encode($pattern['prefNames'], JSON_UNESCAPED_UNICODE),
                        'city'          => $pattern['city'],
                        'gender'        => $pattern['gender'],
                        'age1'          => $pattern['age1'],
                        'age2'          => $pattern['age2'],
                        'age3'          => $pattern['age3'],
                        'device'        => json_encode($pattern['device'], JSON_UNESCAPED_UNICODE),
                        'remarks'       => $pattern['remarks'],
                    ]);
                }
            }
        }

        // メンション先メールを、Slack内部IDに変換（1回だけ）
        $mentionEmail = 'kohei9604@gmail.com';
        $mentionUserId = null;
        $lookupResponse = Http::withToken(env('SLACK_BOT_TOKEN'))
            ->get('https://slack.com/api/users.lookupByEmail', [
                'email' => $mentionEmail,
            ]);
        if ($lookupResponse->json('ok')) {
            $mentionUserId = $lookupResponse->json('user.id');
        }
        $mentionText = $mentionUserId ? "<@{$mentionUserId}>" : '';

        // 通知先（webhook）ごとに、まとめて1通だけ送信する
        foreach ($notifications as $notif) {
            $routing = $notif['routing'];
            $platformLabelJoined = implode('、', $notif['platforms']);

            $departmentSlug = $departmentSlugMap[$routing->receiving_department] ?? null;
            $detailUrl = $departmentSlug
                ? url('/departments/' . $departmentSlug)
                : url('/');

            $messageText = "🏳️ *{$entry['clientName']}* （{$entry['projectName']}）⬛ {$platformLabelJoined}\n"
                . "\n"
                . "{$mentionText}　{$entry['applicantName']}さんからSIM依頼が届きました！\n"
                . "\n"
                . "Page ▷{$detailUrl}";

            Http::post($routing->webhook_url, [
                'text'   => $messageText,
                'mrkdwn' => true,
            ]);
        }

        return response()->json([
            'message' => '保存しました'
        ]);
    }

    public function department($slug)
    {
        $departmentMap = [
            'qm-sem-2' => 'QM-SEM-2ナビ',
            'qm-sem-1' => 'QM-SEM-1ナビ',
            'qm-sns'   => 'QM-SNSナビ',
        ];

        if (!isset($departmentMap[$slug])) {
            abort(404);
        }

        $departmentName = $departmentMap[$slug];

        $campaigns = DB::table('campaigns')
            ->join('entries', 'campaigns.entry_id', '=', 'entries.id')
            ->where('campaigns.receiving_department', $departmentName)
            ->orderByDesc('campaigns.created_at')
            ->select(
                'campaigns.*',
                'entries.email as entry_email',
                'entries.remarks as entry_remarks',
                'entries.due_date as entry_due_date'
            )
            ->get();

        $menuColors = [
            'VRC2.0（リーチ）'              => 'pink',
            'VVC（視聴）'                  => 'orange',
            'スキップ不可'                  => 'red',
            '目標FQ（マルチフォーマット）'    => 'purple',
            '目標FQ（スキップ可）'          => 'blue',
            '目標FQ（スキップ不可）'        => 'green',
        ];

        $jsonColumnsByPlatform = [
            'youtube' => ['placement', 'pref_names', 'gender', 'targeting1_values', 'targeting2_values', 'targeting3_values', 'device'],
            'meta'    => ['placement', 'pref_names'],
            'yg'      => ['pref_names', 'targeting1_values', 'targeting2_values', 'targeting3_values', 'device'],
            'listing' => ['pref_names', 'device'],
        ];

        $platformLabels = [
            'meta'    => 'Meta',
            'youtube' => 'YouTube',
            'yg'      => 'YG-Display&DGC',
            'listing' => 'Listing',
            'x'       => 'X',
            'line'    => 'LINE',
        ];

        $grouped = $campaigns->groupBy(function ($c) {
            return $c->client_name . '｜' . $c->project_name;
        });

        $groups = [];
        foreach ($grouped as $groupCampaigns) {
            $campaignBlocks = [];

            foreach ($groupCampaigns as $campaign) {
                $platform = $campaign->platform;
                $patterns = collect();

                if (in_array($platform, ['youtube', 'meta', 'yg', 'listing'])) {
                    $table = $platform . '_patterns';
                    $patterns = DB::table($table)->where('campaign_id', $campaign->id)->get();
                    $jsonColumns = $jsonColumnsByPlatform[$platform] ?? [];

                    // youtube・meta・yg・listingは見積もり自動計算に対応。estimate_itemsから該当分を先にまとめて取得しておく
                    $estimateItems = DB::table('estimate_items')
                        ->where('platform', $platform)
                        ->whereIn('pattern_id', $patterns->pluck('id'))
                        ->get()
                        ->keyBy('pattern_id');

                    $patterns = $patterns->map(function ($p) use ($jsonColumns, $menuColors, $estimateItems) {
                        foreach ($jsonColumns as $col) {
                            if (isset($p->$col)) {
                                $decoded = json_decode($p->$col);
                                $p->$col = is_array($decoded) ? $decoded : [];
                            }
                        }
                        $p->menu_color = $menuColors[$p->menu ?? ''] ?? 'gray';
                        $p->estimateItem = $estimateItems->get($p->id) ?? null;
                        return $p;
                    });
                }

                $campaignBlocks[] = [
                    'campaign' => $campaign,
                    'patterns' => $patterns,
                ];
            }

            $groups[] = [
                'client_name'  => $groupCampaigns->first()->client_name,
                'project_name' => $groupCampaigns->first()->project_name,
                'campaigns'    => $campaignBlocks,
            ];
        }

        // 納期カレンダー（アジェンダ形式）用のデータ：Assign待ち・対応中のみ、納期日ごとにグループ化
        $agendaSource = $campaigns
            ->whereIn('status', ['Assign待ち', '対応中'])
            ->filter(function ($c) {
                return !empty($c->entry_due_date);
            })
            ->sortBy('entry_due_date');

        $agendaGroups = [];
        foreach ($agendaSource as $c) {
            $agendaGroups[$c->entry_due_date][] = $c;
        }
        ksort($agendaGroups);

        return view('requests.department', [
            'departmentName' => $departmentName,
            'platformLabels' => $platformLabels,
            'groups'         => $groups,
            'agendaGroups'   => $agendaGroups,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $status = $request->input('status');
        $allowedStatuses = ['Assign待ち', '対応中', '送付済み'];

        if (!in_array($status, $allowedStatuses)) {
            return response()->json(['error' => '不正なステータスです'], 422);
        }

        DB::table('campaigns')->where('id', $id)->update(['status' => $status]);

        return response()->json(['message' => '更新しました']);
    }

    public function updateAssignee(Request $request, $id)
    {
        $assignee = trim((string) $request->input('assignee'));

        if ($assignee === '') {
            return response()->json(['error' => '担当者名を入力してください'], 422);
        }

        DB::table('campaigns')->where('id', $id)->update(['assignee' => $assignee]);

        return response()->json(['message' => '更新しました']);
    }

    public function saveEstimate(Request $request, $id)
    {
        $platform = $request->input('platform');

        if (!in_array($platform, ['youtube', 'meta', 'yg', 'listing'])) {
            return response()->json(['error' => '不正なplatformです'], 422);
        }

        $metricKeys = [
            'cpm', 'cpv', 'vcpm', 'cpc', 'cpa',
            'ctr', 'cvr', 'view_rate', 'view_complete_rate', 'vimp_rate',
            'cts', 'imp', 'vimp', 'cv', 'view_count', 'view_complete',
            'fq', 'reach', 'max_budget', 'max_budget_mode',
        ];

        $values = [];
        foreach ($metricKeys as $key) {
            if ($request->has($key)) {
                $raw = $request->input($key);
                $values[$key] = ($raw === '' || $raw === null) ? null : $raw;
            }
        }

        $values['billing']    = $request->input('billing');
        $values['updated_at'] = now();

        $existing = DB::table('estimate_items')
            ->where('platform', $platform)
            ->where('pattern_id', $id)
            ->first();

        if ($existing) {
            DB::table('estimate_items')->where('id', $existing->id)->update($values);
        } else {
            $values['platform']   = $platform;
            $values['pattern_id'] = $id;
            $values['created_at'] = now();
            DB::table('estimate_items')->insert($values);
        }

        return response()->json(['message' => '保存しました']);
    }

    /**
     * 部署ダッシュボードの「メール送信」モーダルから送られてきた内容を、
     * 実際にメールとして送信する。campaign_id が渡ってきた場合は、
     * その案件の入力内容をExcelファイルにまとめてメールに添付する。
     */
    public function sendMail(Request $request)
    {
        $request->validate([
            'to'          => 'required|email',
            'subject'     => 'required|string',
            'body'        => 'required|string',
            'campaign_id' => 'nullable|string',
        ]);

        $mail = new SendMail($request->input('subject'), $request->input('body'));

        // campaign_id は "3,4" のようにカンマ区切りで渡ってくる（1つの案件に複数媒体が紐づく場合があるため）
        $campaignIds = collect(explode(',', (string) $request->input('campaign_id')))
            ->map(fn ($id) => trim($id))
            ->filter()
            ->all();

        if (!empty($campaignIds)) {
            $export = $this->buildEstimateExport($campaignIds);

            // ファイル名に「クライアント名_案件名」を入れる（1つの依頼＝1クライアント/1案件名という前提）
            $firstCampaign = DB::table('campaigns')->whereIn('id', $campaignIds)->first();
            $sanitize = fn ($v) => trim(preg_replace('/[\\\\\/:*?"<>|　\s]+/u', '_', (string) $v), '_');
            $namePart = $firstCampaign
                ? trim($sanitize($firstCampaign->client_name) . '_' . $sanitize($firstCampaign->project_name), '_')
                : '見積り';
            $fileName = ($namePart !== '' ? $namePart : '見積り') . '_' . now()->format('Ymd_His') . '.xlsx';

            $binary = ExcelFacade::raw($export, ExcelFormat::XLSX);
            $mail->attachExcel($binary, $fileName);
        }

        Mail::to($request->input('to'))->send($mail);

        return response()->json(['message' => '送信しました']);
    }

    /**
     * 性別・デバイスの表記ゆれ（'male'/'all' のような英語のまま届く値）を、
     * department.blade.php のYouTubeブロックにある $formatGender / $formatDevice と同じ規則で
     * ダッシュボード表示用の日本語・大文字表記に揃える。
     */
    private function formatGenderValue($value): string
    {
        $g = trim((string) $value);
        $map = [
            'male' => '男性', 'MALE' => '男性', 'Male' => '男性',
            'female' => '女性', 'FEMALE' => '女性', 'Female' => '女性',
            'all' => 'ALL', 'ALL' => 'ALL', 'All' => 'ALL',
        ];
        return $map[$g] ?? $g;
    }

    private function formatDeviceValue($value): string
    {
        return strtoupper(trim((string) $value));
    }

    /**
     * ターゲティングの選択値（文字列配列/オブジェクト配列どちらでも）を表示用文字列に整形する。
     * department.blade.php の $formatTargetingValues と同じロジック。
     */
    private function formatTargetingValuesList($values): string
    {
        if (!is_array($values) || empty($values)) {
            return '';
        }
        $labels = array_map(function ($v) {
            if (is_array($v)) {
                return $v['label'] ?? $v['name'] ?? $v['text'] ?? $v['value'] ?? '';
            }
            if (is_object($v)) {
                $arr = (array) $v;
                return $arr['label'] ?? $arr['name'] ?? $arr['text'] ?? $arr['value'] ?? '';
            }
            return (string) $v;
        }, $values);
        $labels = array_filter($labels, fn ($l) => $l !== '');
        return implode('・', $labels);
    }

    /** DBにJSON文字列で保存されている値を配列に戻す（壊れていれば空配列を返す）。 */
    private function decodeJsonArray($raw): array
    {
        $decoded = json_decode((string) $raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * 指定された campaign_id（複数可）の入力内容を、媒体ごとにシートを分けたExcelブックとして組み立てる。
     * department() の画面表示（department.blade.php）と全く同じ並び順・ラベル・書式ルールになるよう、
     * 媒体（YouTube/Meta/YG-Display&DGC/リスティング）ごとに個別に列を組み立てている。
     */
    private function buildEstimateExport(array $campaignIds): EstimateWorkbookExport
    {
        $platformLabels = [
            'youtube' => 'YouTube',
            'meta'    => 'Meta',
            'yg'      => 'YG-Display&DGC',
            'listing' => 'リスティング',
        ];

        // 見出し行の背景色（ネイビーで統一）
        $headerColor = 'FF1B2A4A';

        // メニュー名から課金形態を判定（department.blade.php のYouTubeブロックと同じ対応表）
        $youtubeBillingMap = [
            'VRC2.0（リーチ）' => 'CPM課金',
            'スキップ不可'     => 'CPM課金',
            'VVC（視聴）'      => 'CPV課金',
        ];
        $resolveYoutubeBilling = fn ($menu) => $youtubeBillingMap[$menu] ?? null;
        $ytFixedCtr = 0.0004; // YouTubeのCTRは常に0.04%固定（ダッシュボードと同じ）

        // YGの「メニュー・課金形態 → 適用される指標キー」判定（department.blade.php のYGブロックと同じ対応表）
        $ygCaseKeys = [
            'cpc'       => ['imp', 'cts', 'ctr', 'cpc', 'cvr', 'cv', 'cpa'],
            'vcpm'      => ['imp', 'vimp', 'vcpm', 'cts', 'ctr', 'cpc', 'cvr', 'cv', 'cpa', 'vimp_rate'],
            'dgc_image' => ['imp', 'cpm', 'cts', 'ctr', 'cpc', 'cvr', 'cv', 'cpa'],
            'dgc_video' => ['imp', 'cpm', 'cts', 'ctr', 'cpc', 'view_rate', 'view_count', 'cvr', 'cv', 'cpa'],
        ];
        $resolveYgCase = function ($menu, $billing) {
            $menu = (string) ($menu ?? '');
            if (stripos($menu, 'DGC') !== false) {
                return (stripos($menu, '動画') !== false) ? 'dgc_video' : 'dgc_image';
            }
            if ($billing === 'vCPM課金') return 'vcpm';
            if ($billing === 'CPC課金') return 'cpc';
            return null;
        };

        // 計算結果が0除算やテキストで壊れても必ず「—」に逃がす、Excel数式組み立て用の小さなヘルパー
        $wrap = fn (string $expr): string => "=IFERROR({$expr},\"—\")";

        $campaigns = DB::table('campaigns')->whereIn('id', $campaignIds)->get();

        $sheets = [];

        foreach (['youtube', 'meta', 'yg', 'listing'] as $platform) {
            $platformCampaigns = $campaigns->where('platform', $platform);
            if ($platformCampaigns->isEmpty()) {
                continue; // この媒体の案件が含まれていなければ、シート自体を作らない
            }

            // 列定義（[キー, 見出し, 書式タイプ]）。全媒体で
            // メニュー→広告フォーマット→デバイス→課金形態→ターゲティング→エリア→掲載期間→予算→指標…の順に統一する。
            if ($platform === 'youtube') {
                $specs = [
                    ['menu', 'メニュー', null], ['placement', '配信面', null], ['creative', '広告素材', null], ['device', 'デバイス', null],
                    ['billing', '課金形態', null], ['age', '年齢', null], ['gender', '性別', null],
                    ['targeting1', 'ターゲティング①', null], ['targeting2', 'ターゲティング②', null], ['targeting3', 'ターゲティング③', null],
                    ['area', 'エリア', null], ['period', '掲載期間', null], ['budget', '予算', 'currency'],
                    ['imp', 'IMP', 'number'], ['cpm', 'CPM', 'currency'], ['cts', 'CTs', 'number'], ['ctr', 'CTR', 'percent'], ['cpc', 'CPC', 'currency'],
                    ['view_rate', '視聴率', 'percent'], ['view_count', '視聴数', 'number'], ['cpv', 'CPV', 'currency'],
                    ['view_complete_rate', '視聴完了率', 'percent'], ['view_complete', '視聴完了数', 'number'],
                    ['cvr', 'CVR', 'percent'], ['cv', 'CVs', 'number'], ['cpa', 'CPA', 'currency'], ['fq', '想定FQ', 'decimal'], ['reach', 'リーチ数', 'number'],
                    ['notes', '備考', null], ['max_budget', 'MAX出稿金額', null],
                ];
            } elseif ($platform === 'meta') {
                $specs = [
                    ['campaign_objective', 'キャンペーン目的', null], ['kpi', 'KPI', null], ['menu', 'メニュー', null], ['placement', '配信面', null],
                    ['device', 'デバイス', null], ['billing', '課金形態', null], ['age', '年齢', null], ['gender', '性別', null],
                    ['area', 'エリア', null], ['period', '配信期間', null], ['budget', '予算', 'currency'],
                    ['vcpm', 'vCPM', 'currency'], ['vimp_rate', '推定vimp率', 'percent'], ['ctr', 'CTR', 'percent'], ['cpc', 'CPC', 'currency'], ['cvr', 'CVR', 'percent'],
                    ['vimp', 'vIMP(想定)', 'number'], ['imp', 'IMP(想定)', 'number'], ['cts', 'CTs', 'number'], ['cv', 'CV', 'number'], ['cpa', 'CPA', 'currency'],
                    ['remarks', '備考', null],
                ];
            } elseif ($platform === 'yg') {
                $specs = [
                    ['menu', 'メニュー', null], ['video_duration', '動画尺', null], ['device', 'デバイス', null],
                    ['billing', '課金形態', null], ['age', '年齢', null], ['gender', '性別', null],
                    ['targeting1', 'ターゲティング①', null], ['targeting2', 'ターゲティング②', null], ['targeting3', 'ターゲティング③', null],
                    ['area', 'エリア', null], ['period', '配信期間', null], ['budget', '予算', 'currency'],
                    ['imp', 'IMP', 'number'], ['vimp', 'vIMP', 'number'], ['vcpm', 'vCPM', 'currency'], ['cpm', 'CPM', 'currency'], ['cts', 'CTs', 'number'],
                    ['ctr', 'CTR', 'percent'], ['cpc', 'CPC', 'currency'], ['view_rate', '視聴率', 'percent'], ['view_count', '視聴数', 'number'],
                    ['cvr', 'CVR', 'percent'], ['cv', 'CVs', 'number'], ['cpa', 'CPA', 'currency'], ['vimp_rate', '推定vimp率', 'percent'],
                    ['remarks', '備考', null],
                ];
            } else { // listing
                $specs = [
                    ['listing_request_types', '依頼種別', null], ['listing_lp', 'LP', null], ['listing_kw_broad', 'KW(部分一致)', null],
                    ['listing_kw_phrase', 'KW(フレーズ一致)', null], ['listing_kw_exact', 'KW(完全一致)', null],
                    ['menu', 'メニュー', null], ['device', 'デバイス', null], ['age', '年齢', null], ['gender', '性別', null],
                    ['area', 'エリア', null], ['period', '配信期間', null], ['budget', '予算', 'currency'],
                    ['imp', 'IMP', 'number'], ['cts', 'CTs', 'number'], ['ctr', 'CTR', 'percent'], ['cpc', 'CPC', 'currency'],
                    ['cvr', 'CVR', 'percent'], ['cv', 'CVs', 'number'], ['cpa', 'CPA', 'currency'],
                    ['remarks', '備考', null],
                ];
            }

            $headings = array_map(fn ($s) => $s[1], $specs);
            $columnTypes = array_map(fn ($s) => $s[2], $specs);

            // 列キー → 列文字（A, B, ...）の対応表。数式で「他の列の同じ行」を参照するために使う
            $colLetter = [];
            foreach ($specs as $i => $s) {
                $colLetter[$s[0]] = Coordinate::stringFromColumnIndex($i + 1);
            }

            $rows = [];

            foreach ($platformCampaigns as $campaign) {
                $table = $platform . '_patterns';
                $patterns = DB::table($table)->where('campaign_id', $campaign->id)->get();

                $estimateItems = DB::table('estimate_items')
                    ->where('platform', $platform)
                    ->whereIn('pattern_id', $patterns->pluck('id'))
                    ->get()
                    ->keyBy('pattern_id');

                foreach ($patterns as $p) {
                    $est = $estimateItems->get($p->id);

                    // このパターンが実際に書き込まれるExcelの行番号（PlatformPatternSheetのデータ開始行に合わせる）
                    $excelRow = PlatformPatternSheet::DATA_START_ROW + count($rows);
                    $ref = fn (string $key) => ($colLetter[$key] ?? '') . $excelRow;

                    $vals = []; // key => 値（この1行分。$specs と同じキーをすべて埋める）

                    if ($platform === 'youtube') {
                        $vals['menu'] = $p->menu;
                        $vals['placement'] = implode('、', $this->decodeJsonArray($p->placement ?? ''));
                        $vals['creative'] = '縦' . ($p->vertical_creative ?? '') . ' / 横' . ($p->horizontal_creative ?? '');
                        $vals['device'] = implode('/', array_map(fn ($d) => $this->formatDeviceValue($d), $this->decodeJsonArray($p->device ?? '')));
                        $ytBilling = $resolveYoutubeBilling($p->menu ?? '');
                        $vals['billing'] = $ytBilling ?? '判定不可';
                        $vals['age'] = ($p->age1 ?? '') . ($p->age2 ?? '');
                        $vals['gender'] = implode('/', array_map(fn ($g) => $this->formatGenderValue($g), $this->decodeJsonArray($p->gender ?? '')));
                        // 「なし」（=未選択）はターゲティングとして扱わず、空欄のまま表示する
                        foreach ([1, 2, 3] as $n) {
                            $type = $p->{"targeting{$n}_type"} ?? null;
                            $vals["targeting{$n}"] = ($type && $type !== 'なし')
                                ? $type . '：' . $this->formatTargetingValuesList($this->decodeJsonArray($p->{"targeting{$n}_values"} ?? ''))
                                : '—';
                        }
                        $vals['area'] = implode('、', $this->decodeJsonArray($p->pref_names ?? '')) . ($p->city ? '・' . $p->city : '');
                        $vals['period'] = ($p->period_number ?? '') . ($p->period_unit ?? '');
                        $vals['budget'] = $p->budget;

                        // CTRは常に0.04%固定（ダッシュボードと同じ）、想定FQは課金形態に関わらず手入力
                        $vals['ctr'] = $ytFixedCtr;
                        $vals['fq'] = ($est && isset($est->fq)) ? $est->fq : '—';

                        if ($ytBilling === 'CPV課金') {
                            // 入力：CPV・視聴率・視聴完了率・CVR。それ以外はダッシュボードの計算式と同じ内容のExcel数式にする
                            $vals['cpv'] = ($est && isset($est->cpv)) ? $est->cpv : '—';
                            $vals['view_rate'] = ($est && isset($est->view_rate)) ? $est->view_rate : '—';
                            $vals['view_complete_rate'] = ($est && isset($est->view_complete_rate)) ? $est->view_complete_rate : '—';
                            $vals['cvr'] = ($est && isset($est->cvr)) ? $est->cvr : '—';
                            $vals['view_count'] = $wrap("{$ref('budget')}/{$ref('cpv')}");
                            $vals['imp'] = $wrap("{$ref('view_count')}/{$ref('view_rate')}");
                            $vals['cts'] = $wrap("{$ref('imp')}*{$ref('ctr')}");
                            $vals['cpc'] = $wrap("IF({$ref('cts')}>0,{$ref('budget')}/{$ref('cts')},\"—\")");
                            $vals['view_complete'] = $wrap("{$ref('imp')}*{$ref('view_complete_rate')}");
                            $vals['cv'] = $wrap("{$ref('cts')}*{$ref('cvr')}");
                            $vals['cpa'] = $wrap("IF({$ref('cv')}>0,{$ref('budget')}/{$ref('cv')},\"—\")");
                            $vals['reach'] = $wrap("IF({$ref('fq')}>0,{$ref('imp')}/{$ref('fq')},\"—\")");
                            $vals['cpm'] = '—';
                        } elseif ($ytBilling === 'CPM課金') {
                            $vals['cpm'] = ($est && isset($est->cpm)) ? $est->cpm : '—';
                            $vals['view_rate'] = ($est && isset($est->view_rate)) ? $est->view_rate : '—';
                            $vals['view_complete_rate'] = ($est && isset($est->view_complete_rate)) ? $est->view_complete_rate : '—';
                            $vals['cvr'] = ($est && isset($est->cvr)) ? $est->cvr : '—';
                            $vals['imp'] = $wrap("{$ref('budget')}/{$ref('cpm')}*1000");
                            $vals['cts'] = $wrap("{$ref('imp')}*{$ref('ctr')}");
                            $vals['cpc'] = $wrap("IF({$ref('cts')}>0,{$ref('budget')}/{$ref('cts')},\"—\")");
                            $vals['view_count'] = $wrap("{$ref('imp')}*{$ref('view_rate')}");
                            $vals['view_complete'] = $wrap("{$ref('imp')}*{$ref('view_complete_rate')}");
                            $vals['cv'] = $wrap("{$ref('cts')}*{$ref('cvr')}");
                            $vals['cpa'] = $wrap("IF({$ref('cv')}>0,{$ref('budget')}/{$ref('cv')},\"—\")");
                            $vals['reach'] = $wrap("IF({$ref('fq')}>0,{$ref('imp')}/{$ref('fq')},\"—\")");
                            $vals['cpv'] = '—';
                        } else {
                            foreach (['imp', 'cpm', 'cts', 'cpc', 'view_rate', 'view_count', 'cpv', 'view_complete_rate', 'view_complete', 'cvr', 'cv', 'cpa', 'reach'] as $k) {
                                $vals[$k] = '—';
                            }
                        }

                        $maxBudgetMode = ($est && ($est->max_budget_mode ?? null) === 'unlimited') ? 'unlimited' : 'amount';
                        $vals['max_budget'] = $maxBudgetMode === 'unlimited'
                            ? '億単位で出稿可'
                            : (($est && isset($est->max_budget) && $est->max_budget !== null) ? '¥' . number_format((float) $est->max_budget) : '');
                        $vals['notes'] = $p->notes ?: '—';
                    } elseif ($platform === 'meta') {
                        $vals['campaign_objective'] = $p->campaign_objective;
                        $vals['kpi'] = $p->kpi;
                        $vals['menu'] = $p->menu;
                        $vals['placement'] = implode('、', $this->decodeJsonArray($p->placement ?? ''));
                        $vals['device'] = $this->formatDeviceValue($p->device ?? '');
                        $vals['billing'] = $p->billing;
                        $vals['age'] = ($p->age1 ?? '') . '〜' . ($p->age2 ?? '') . '歳';
                        $vals['gender'] = $this->formatGenderValue($p->gender ?? '');
                        $vals['area'] = implode('、', $this->decodeJsonArray($p->pref_names ?? '')) . ($p->city ? '・' . $p->city : '');
                        $vals['period'] = ($p->period_number ?? '') . ($p->period_unit ?? '');
                        $vals['budget'] = $p->budget;

                        foreach (['vcpm', 'vimp_rate', 'ctr', 'cpc', 'cvr', 'vimp', 'imp', 'cts', 'cv', 'cpa'] as $k) {
                            $vals[$k] = '—';
                        }

                        if ($p->billing === 'CPC課金') {
                            $vals['ctr'] = ($est && isset($est->ctr)) ? $est->ctr : '—';
                            $vals['cpc'] = ($est && isset($est->cpc)) ? $est->cpc : '—';
                            $vals['cvr'] = ($est && isset($est->cvr)) ? $est->cvr : '—';
                            $vals['cts'] = $wrap("{$ref('budget')}/{$ref('cpc')}");
                            $vals['imp'] = $wrap("{$ref('cts')}/{$ref('ctr')}");
                            $vals['cv']  = $wrap("{$ref('cts')}*{$ref('cvr')}");
                            $vals['cpa'] = $wrap("IF({$ref('cv')}>0,{$ref('budget')}/{$ref('cv')},\"—\")");
                        } elseif ($p->billing === 'vCPM課金') {
                            $vals['vcpm'] = ($est && isset($est->vcpm)) ? $est->vcpm : '—';
                            $vals['ctr'] = ($est && isset($est->ctr)) ? $est->ctr : '—';
                            $vals['cvr'] = ($est && isset($est->cvr)) ? $est->cvr : '—';
                            $vals['vimp_rate'] = ($est && isset($est->vimp_rate)) ? $est->vimp_rate : '—';
                            $vals['vimp'] = $wrap("{$ref('budget')}/{$ref('vcpm')}*1000");
                            $vals['imp']  = $wrap("{$ref('vimp')}/{$ref('vimp_rate')}");
                            $vals['cts']  = $wrap("{$ref('vimp')}*{$ref('ctr')}");
                            $vals['cpc']  = $wrap("IF({$ref('cts')}>0,{$ref('budget')}/{$ref('cts')},\"—\")");
                            $vals['cv']   = $wrap("{$ref('cts')}*{$ref('cvr')}");
                            $vals['cpa']  = $wrap("IF({$ref('cv')}>0,{$ref('budget')}/{$ref('cv')},\"—\")");
                        }

                        $vals['remarks'] = $p->remarks ?: '—';
                    } elseif ($platform === 'yg') {
                        $ygCase = $resolveYgCase($p->menu ?? '', $p->billing ?? null);
                        $vals['menu'] = $p->menu;
                        $vals['video_duration'] = $p->video_duration ? $p->video_duration . '秒' : '—';
                        $vals['device'] = implode('/', array_map(fn ($d) => $this->formatDeviceValue($d), $this->decodeJsonArray($p->device ?? '')));
                        $vals['billing'] = $p->billing ?: ($ygCase && str_starts_with($ygCase, 'dgc') ? 'DGC' : '—');
                        $vals['age'] = ($p->age1 ?? '') . ($p->age2 ?? '');
                        $vals['gender'] = $p->gender;

                        // 「なし」（=未選択）はターゲティングとして扱わず、空欄のまま表示する
                        foreach ([1, 2, 3] as $n) {
                            $type = $p->{"targeting{$n}_type"} ?? null;
                            $vals["targeting{$n}"] = ($type && $type !== 'なし')
                                ? $type . '：' . implode('/', $this->decodeJsonArray($p->{"targeting{$n}_values"} ?? ''))
                                : '—';
                        }

                        $vals['area'] = implode('、', $this->decodeJsonArray($p->pref_names ?? '')) . ($p->city ? '・' . $p->city : '');
                        $vals['period'] = ($p->period_number ?? '') . ($p->period_unit ?? '');
                        $vals['budget'] = $p->budget;

                        foreach (['imp', 'vimp', 'vcpm', 'cpm', 'cts', 'ctr', 'cpc', 'view_rate', 'view_count', 'cvr', 'cv', 'cpa', 'vimp_rate'] as $k) {
                            $vals[$k] = '—';
                        }

                        if ($ygCase === 'cpc') {
                            $vals['ctr'] = ($est && isset($est->ctr)) ? $est->ctr : '—';
                            $vals['cpc'] = ($est && isset($est->cpc)) ? $est->cpc : '—';
                            $vals['cvr'] = ($est && isset($est->cvr)) ? $est->cvr : '—';
                            $vals['cts'] = $wrap("{$ref('budget')}/{$ref('cpc')}");
                            $vals['imp'] = $wrap("{$ref('cts')}/{$ref('ctr')}");
                            $vals['cv']  = $wrap("{$ref('cts')}*{$ref('cvr')}");
                            $vals['cpa'] = $wrap("IF({$ref('cv')}>0,{$ref('budget')}/{$ref('cv')},\"—\")");
                        } elseif ($ygCase === 'vcpm') {
                            $vals['vcpm'] = ($est && isset($est->vcpm)) ? $est->vcpm : '—';
                            $vals['ctr'] = ($est && isset($est->ctr)) ? $est->ctr : '—';
                            $vals['cvr'] = ($est && isset($est->cvr)) ? $est->cvr : '—';
                            $vals['vimp_rate'] = ($est && isset($est->vimp_rate)) ? $est->vimp_rate : '—';
                            $vals['vimp'] = $wrap("{$ref('budget')}/{$ref('vcpm')}*1000");
                            $vals['imp']  = $wrap("{$ref('vimp')}/{$ref('vimp_rate')}");
                            $vals['cts']  = $wrap("{$ref('vimp')}*{$ref('ctr')}");
                            $vals['cpc']  = $wrap("IF({$ref('cts')}>0,{$ref('budget')}/{$ref('cts')},\"—\")");
                            $vals['cv']   = $wrap("{$ref('cts')}*{$ref('cvr')}");
                            $vals['cpa']  = $wrap("IF({$ref('cv')}>0,{$ref('budget')}/{$ref('cv')},\"—\")");
                        } elseif ($ygCase === 'dgc_image') {
                            $vals['ctr'] = ($est && isset($est->ctr)) ? $est->ctr : '—';
                            $vals['cpc'] = ($est && isset($est->cpc)) ? $est->cpc : '—';
                            $vals['cvr'] = ($est && isset($est->cvr)) ? $est->cvr : '—';
                            $vals['cts'] = $wrap("{$ref('budget')}/{$ref('cpc')}");
                            $vals['imp'] = $wrap("{$ref('cts')}/{$ref('ctr')}");
                            $vals['cpm'] = $wrap("IF({$ref('imp')}>0,{$ref('budget')}/{$ref('imp')}*1000,\"—\")");
                            $vals['cv']  = $wrap("{$ref('cts')}*{$ref('cvr')}");
                            $vals['cpa'] = $wrap("IF({$ref('cv')}>0,{$ref('budget')}/{$ref('cv')},\"—\")");
                        } elseif ($ygCase === 'dgc_video') {
                            $vals['ctr'] = ($est && isset($est->ctr)) ? $est->ctr : '—';
                            $vals['cpc'] = ($est && isset($est->cpc)) ? $est->cpc : '—';
                            $vals['view_rate'] = ($est && isset($est->view_rate)) ? $est->view_rate : '—';
                            $vals['cvr'] = ($est && isset($est->cvr)) ? $est->cvr : '—';
                            $vals['cts'] = $wrap("{$ref('budget')}/{$ref('cpc')}");
                            $vals['imp'] = $wrap("{$ref('cts')}/{$ref('ctr')}");
                            $vals['cpm'] = $wrap("IF({$ref('imp')}>0,{$ref('budget')}/{$ref('imp')}*1000,\"—\")");
                            $vals['view_count'] = $wrap("{$ref('imp')}*{$ref('view_rate')}");
                            $vals['cv']  = $wrap("{$ref('cts')}*{$ref('cvr')}");
                            $vals['cpa'] = $wrap("IF({$ref('cv')}>0,{$ref('budget')}/{$ref('cv')},\"—\")");
                        }

                        $vals['remarks'] = $p->remarks ?: '—';
                    } else { // listing
                        $vals['listing_request_types'] = implode('、', $this->decodeJsonArray($campaign->listing_request_types ?? ''));
                        $vals['listing_lp'] = $campaign->listing_lp;
                        $vals['listing_kw_broad'] = $campaign->listing_kw_broad;
                        $vals['listing_kw_phrase'] = $campaign->listing_kw_phrase;
                        $vals['listing_kw_exact'] = $campaign->listing_kw_exact;
                        $vals['menu'] = $p->menu;
                        $vals['device'] = implode('/', array_map(fn ($d) => $this->formatDeviceValue($d), $this->decodeJsonArray($p->device ?? '')));
                        $vals['age'] = ($p->age1 ?? '') . ($p->age2 ?? '') . ($p->age3 ? '（' . $p->age3 . '）' : '');
                        $vals['gender'] = $p->gender;
                        $vals['area'] = implode('、', $this->decodeJsonArray($p->pref_names ?? '')) . ($p->city ? '・' . $p->city : '');
                        $vals['period'] = ($p->period_number ?? '') . ($p->period_unit ?? '');
                        $vals['budget'] = $p->budget;

                        // リスティングは常にCTR・CPC・CVRが入力欄（ダッシュボードと同じ）
                        $vals['ctr'] = ($est && isset($est->ctr)) ? $est->ctr : '—';
                        $vals['cpc'] = ($est && isset($est->cpc)) ? $est->cpc : '—';
                        $vals['cvr'] = ($est && isset($est->cvr)) ? $est->cvr : '—';
                        $vals['cts'] = $wrap("{$ref('budget')}/{$ref('cpc')}");
                        $vals['imp'] = $wrap("{$ref('cts')}/{$ref('ctr')}");
                        $vals['cv']  = $wrap("{$ref('cts')}*{$ref('cvr')}");
                        $vals['cpa'] = $wrap("IF({$ref('cv')}>0,{$ref('budget')}/{$ref('cv')},\"—\")");

                        $vals['remarks'] = $p->remarks ?: '—';
                    }

                    $rows[] = array_map(fn ($s) => $vals[$s[0]] ?? '—', $specs);
                }
            }

            $firstCampaign = $platformCampaigns->first();
            // クライアント名・案件名は見出し文言なしでそのまま表示（誰が見ても分かるため「クライアント名：」等は付けない）
            $summaryLine = $firstCampaign->client_name . '（' . $firstCampaign->project_name . '）';

            $sheets[] = new PlatformPatternSheet(
                rows: $rows,
                headings: $headings,
                title: $platformLabels[$platform],
                headerColor: $headerColor,
                columnTypes: $columnTypes,
                summaryLine: $summaryLine,
            );
        }

        return new EstimateWorkbookExport($sheets);
    }
}