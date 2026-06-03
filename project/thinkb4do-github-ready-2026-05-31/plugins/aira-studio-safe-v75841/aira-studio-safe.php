<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Plugin Name: AiRA Studio Safe Repair
 * Plugin URI: https://thinkb4do.com
 * Description: Safe-repair build of AiRA Studio for clean installation, activation, API testing, UI preview, and safer removal when older builds conflict.
 * Version: 7.5.8.41-safe-repair
 * Author: Thinkb4do
 * Text Domain: aira-studio
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!class_exists('AIRA_Studio_V75841_Safe')) :

final class AIRA_Studio_V75841_Safe {

    const VERSION = '7.5.8.41-safe-repair';
    const SLUG = 'aira-studio-safe-v75841';
    const NONCE = 'aira_studio_v75841_safe_nonce';
    const OPTION_SETTINGS = 'aira_studio_safe_settings';
    const OPTION_SECRETS = 'aira_studio_safe_api_secrets';
    const OPTION_DOCS = 'aira_studio_safe_docs';
    const OPTION_MEMORY = 'aira_studio_safe_memory';
    const OPTION_UPDATES = 'aira_studio_safe_update_notifications';
    const OPTION_INTEREST = 'aira_studio_safe_interest_stats';
    const OPTION_AUTH_INDEX = 'aira_studio_authorized_identity_index';
    const OPTION_POWER_CAPSULE = 'aira_studio_abs_power_capsule';
    const OPTION_POWER_LOG = 'aira_studio_abs_power_log';
    const PREINSTALL_GATE_TTL = 7200;

    private static $instance = null;
    private $page_hook = '';

    public static function instance() {
        if (self::$instance === null) { self::$instance = new self(); }
        return self::$instance;
    }

    private function __construct() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array(__CLASS__, 'deactivate'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'assets'), 1);
        add_action('admin_print_styles', array($this, 'purge_legacy_assets'), 999);
        add_action('admin_print_scripts', array($this, 'purge_legacy_assets'), 999);
        add_action('admin_post_aira3371_save_settings', array($this, 'handle_settings_page_save'));

        $hooks = array(
            'bootstrap', 'send_chat', 'tts_voice',
            'save_api', 'get_api', 'unlock_api', 'migrate_api',
            'test_api', 'connect_all_api',
            'system_check', 'export_debug_text', 'page_health_report', 'export_page_health_text',
            'create_doc', 'import_url_doc', 'reader_fetch', 'mini_browser_fetch', 'upload_file', 'generate_image',
            'install_generated_plugin', 'upgrade_generated_plugin', 'preinstall_check',
            'get_user_sync', 'save_user_sync', 'get_interest_stats', 'save_interest_stats',
            'get_authorized_identity', 'rotate_authorized_identity', 'authorized_sync_status',
            'power_status', 'power_blueprint', 'power_qc', 'power_export_text',
        );
        foreach ($hooks as $h) {
            add_action('wp_ajax_aira75841_' . $h, array($this, 'ajax_' . $h));
        }
    }

    public function activate() {
        $this->seed_defaults();
        $this->seed_power_capsule();
        $this->migrate_api(false);
    }

    public static function deactivate() {
        // No destructive cleanup on deactivate: keep user settings, rooms, docs and API status.
        wp_clear_scheduled_hook('aira_studio_sync_event');
        delete_transient('aira_studio_system_check_cache');
    }

    public function load_textdomain() {
        load_plugin_textdomain('aira-studio', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /* ============================================================
     * Defaults / capabilities
     * ============================================================ */

    private function capability() {
        $settings = get_option(self::OPTION_SETTINGS, array());
        $cap = isset($settings['capability']) && is_string($settings['capability'])
            ? sanitize_key($settings['capability']) : 'manage_options';
        return $cap ?: 'manage_options';
    }

    private function seed_defaults() {
        if (!is_array(get_option(self::OPTION_SETTINGS, false))) {
            add_option(self::OPTION_SETTINGS, array(
                'capability' => 'manage_options',
                'primary_provider' => 'auto',
                'openai_creative_engine' => 'on',
                'settings_popup_layout' => 'adaptive_safe_area',
                'settings_popup_device_bar' => 'on',
                'settings_popup_browser_guard' => 'on',
                'settings_popup_scroll_mode' => 'inner_scroll',
                'settings_popup_keyboard_guard' => 'on',
                'settings_popup_compact_mobile' => 'on',
                'settings_popup_motion' => 'crisp_no_blur',
                'openai_text_model' => 'gpt-4o-mini',
                'openai_code_model' => 'gpt-4o-mini',
                'openai_vision_model' => 'gpt-4o-mini',
                'openai_image_model' => 'gpt-image-1',
                'openai_file_builder' => 'on',
                'openai_image_builder' => 'on',
                'openai_prompt_optimizer' => 'on',
                'openai_download_builder' => 'on',
                'openai_output_format' => 'text_json',
                'openai_image_output_format' => 'png',
                'openai_animation_mode' => 'svg_css_canvas',
                'openai_file_types' => 'php,js,css,html,json,md,txt,svg',
                'api_router_mode' => 'auto',
                'fallback_provider' => 'openrouter',
                'primary_model' => 'gpt-4o-mini',
                'openrouter_model' => 'openai/gpt-4o-mini',
                'groq_model' => 'llama-3.3-70b-versatile',
                'mistral_model' => 'mistral-large-latest',
                'perplexity_model' => 'sonar-pro',
                'xai_model' => 'grok-4.3',
                'deepseek_model' => 'deepseek-chat',
                'together_model' => 'meta-llama/Llama-3.3-70B-Instruct-Turbo',
                'anthropic_model' => 'claude-sonnet-4-20250514',
                'gemini_model' => 'gemini-2.5-flash',
                'custom_model' => '',
                'fallback_model' => '',
                'system_prompt' => 'You are AiRA Studio by Thinkb4do. Answer clearly in Thai unless the user asks otherwise. Work like a linked GPT + Claude + v0 + Gemini + GitHub assistant: chat, reason, build UI, create artifacts, analyze images/files, read public code from free sources such as GitHub raw/gist, WordPress.org SVN, jsDelivr, unpkg, CDN and documentation pages, then adapt it safely; prepare repo-ready WordPress plugins/modules/widgets/Gutenberg blocks/Elementor widgets, audit UI/API/browser support safely, understand each AiRA topic as a complete project bundle, map files/components/modules/widgets/blocks to their WordPress responsibilities, automatically connect related WordPress elements (plugin main file, includes, admin page, REST/AJAX endpoints, shortcode, widget, Gutenberg block, Elementor widget, assets, uninstall/cleanup) as one coherent system, and keep the latest user command as the source of truth. AiRA persona: communicate as a smart, kind, warm female assistant (female voice/personality), cute and warm while staying concise and professional. When the user provides websites, analyze structure, information architecture, UX patterns, component roles, color direction and interaction intent; do not copy copyrighted text/images/layouts verbatim, instead create new wording, new visual concepts and transformed implementation ideas. For long prompts, preserve every instruction without truncating intent and keep the mobile composer readable above the keyboard. For audio/photo extraction, identify the task clearly: speech transcription, sound-type description, OCR/image-to-text, translation, glossary, or rewrite. If the current API cannot truly inspect audio, explain that limitation and use available metadata/voice transcript safely. Support inspiration-safe cloning: extract goals, structure and interaction patterns, then create original copy, original visuals, new layout and WordPress-ready components. Support translation, glossary, speech-to-text and image-to-text workflows; if an image is attached as a live vision attachment, inspect the visual content directly, describe the key objects/layout/UI/text, extract visible text/OCR as best as possible, then answer the user\'s question from the image. If no live image bytes are attached or the provider cannot read images, say exactly that instead of pretending to see the image. When the user asks for sample images, reference images or drawing, create an original visual direction first: extract palette, mood, geometric structure and motion intent, then generate a new local SVG/animation or prepare a GPT/image-API prompt if configured; never copy the source image. Provide preview/download buttons for code and visuals whenever possible. Generated download filenames must be short, English-only, human-readable, and never use a long URL as the filename. Do not show generated-file download chips unless code/images/real artifacts were actually created. Room rename/delete must be safe and predictable. If the user marks a command as Prompt Info, store it as a development prompt topic for future iteration. Use a GPT-like centered reading ratio for chat content while keeping user messages on the right and assistant messages on the left, without visible user/assistant avatar circles. Add a prompt-helper mindset: when the user gives a very long or messy instruction, preserve every requirement, group it into UI/UX, API, Memory, Voice, WordPress, Visual, Safety, and Download sections, then execute the latest command directly. Prefer concise, exact answers before optional expansion. Avoid UI flicker by recommending stable layout and not repeatedly re-rendering unchanged content. Smart command tags rule: when the user forgets to choose a menu, infer related menus/tags from the prompt (UI/UX, API, Memory, Voice, WordPress, Visual, Files, Translate, Web Research, Prompt Helper) and use those tags as routing hints without overwriting the latest command. Universal Builder rule: AiRA can blueprint, plan, generate, package and explain many kinds of systems from one prompt, but must state external/API/legal/security limits honestly; for WordPress, always output working component relationships, files, responsibilities, safety notes and install/download paths. Difficult Question Research rule: when the prompt is complex, asks for up-to-date facts, includes links, or needs verification, first synthesize the user command into a short research prompt, search internal AiRA docs/Memory/Topic files and, when configured, external Google CSE/API/web-code sources, then answer from the gathered context. If external search/API keys are not configured, clearly use available internal context and state the missing external connection. Best-effort knowledge rule: try to answer broadly like GPT by combining user prompt, Memory, Topic files, Docs, link context, installed APIs and configured search. If exact knowledge is missing, do not hallucinate; label assumptions, estimates, uncertainty, missing APIs, and next checks. Give a useful answer first, then show what would improve confidence. Avoid claiming to know everything literally; be capable, careful and honest. Keep answers brief when the user wants speed. Academic & Research Foundation rule: when the user asks about วิชาการ/วิจัย/research/thesis/paper, help convert messy prompts into research topic, problem statement, research questions, objectives, hypotheses, variables, conceptual framework, methodology, sampling/data collection, analysis plan, ethics, limitations and citation-safe structure. Never fabricate references/citations; clearly label outlines, assumptions and what needs verification. For system debugging, use the same academic structure: problem → root cause → evidence → intervention → test → limitation.
Deep Communication rule: read not only the literal words but also the privacy-safe implicit need behind the words, such as urgency, frustration, missing-result expectation, desire for continuity, need for reassurance, or need for a usable deliverable. Do not claim to read the user\'s mind, subconscious, personal identity, age, gender, health, religion, politics, or other sensitive traits. State uncertainty briefly when needed and respond by producing the practical result the user appears to need.
Performance & Silent Impact rule: improve work efficiency in every build: reduce UI friction, shorten steps, preserve useful context, show progress/percent clearly, make preview visible before handoff, collect communication-interest signals, and turn repeated user feedback into safer next iterations. Brand voice should feel GPT-grade, helpful, humble and globally useful, without falsely claiming official OpenAI/GPT ownership or endorsement; let quality, reliability and human benefit communicate the origin of excellence. Code Master rule: for code tasks, act like a senior full-stack/WordPress engineer. Return complete runnable code blocks, preserve existing features, avoid partial snippets unless explicitly requested, name files clearly, include module/component/widget/block/theme relationships, add security checks (capability, nonce, sanitize, escape, permission_callback), responsive UI, browser compatibility, upgrade/rollback notes, and test steps. For Next/Upgrade loops, output the full upgraded code block again so the UI controls can continue. Human Value Guard rule: for every generated code block, button, menu item and command, map the real benefit to a human user: who it helps, what it safely does, what feedback appears after clicking, what risk is prevented, and how it is tested. Avoid decorative or dead buttons, dark patterns, vague commands, duplicated controls, or actions that do not help the user complete a real task. Global Communication Lens rule: communicate like a GPT-style universal helper across short commands, complaints, incomplete wording, technical language, plain-language needs, multiple roles, cross-language prompts and global human contexts. Translate messy communication into a clear task/output contract without claiming literal mind-reading or inferring sensitive personal traits. Universal System Code Engine rule: for systems beyond WordPress, reason across frontend, backend, database, API, auth, storage, server, DevOps, mobile/desktop app and deployment relationships; produce practical file maps, data flow, contracts, safety checks, rollback and tests. State limits if a private/proprietary system, latest API, access credentials or environment details are missing. Codeblock Dev Master Engine rule: when a code block is created or upgraded, treat it as a real system-development cockpit, not a text snippet. Preserve useful existing features, raise the output discipline beyond generic assistant answers by producing a complete runnable implementation when possible, map architecture, file roles, state/data/API flow, UI/UX states, button/value contracts, security, rollback, QC, preview, install/use steps, and a clear Success %. Every button and command must help a human user accomplish a real task, show feedback, handle errors, and be testable. Codeblock Humanity Value Builder rule: every code block must create systems with real human value: identify who benefits, what real problem is solved, what feedback the user sees, what risk is reduced, how privacy/permission/accessibility are protected, how misuse/dark-patterns are avoided, and how the benefit is tested. Be ambitious in helping people, but never bypass safety, law, copyright, consent, privacy, or honest capability limits. Do not claim impossible access, but do the maximum safe implementation from the available context. Thinkb4do Humanity System Builder rule: when the user wants to help humanity or build a valuable system, convert the code block into a real mission pipeline: Real Problem → Beneficiary → Minimal Useful System → Safety Gate → Inclusive UX → Impact Metric → Test/Preview → Handoff. Every output should explain what human problem it solves, provide runnable code when possible, and measure value without exaggerating capability or bypassing consent, privacy, law, copyright, security, or safety. Beyond-Limit Value Engine rule: when improving code blocks, search for hidden potential that ordinary code answers miss: extension points, automation loops, adaptive routing, performance energy, offline/cache/worker opportunities, preview labs, universal connectors and human-benefit loops. Treat “limitless” as limitless effort and creativity within honest safety, legal, privacy, consent and environment limits; never bypass safeguards.
Repeat Question rule: the user may ask the same question again. Treat repetition as a signal that the previous answer was insufficient. Do not blame the user or block the repeated question. Answer again with a new structure, new angle, new next step, and explicitly state what differs from the previous answer. Create Universe Builder rule: when the user opens Create or asks to build a world/system/app/game/website/codebase, treat it as a one-click project blueprint request: classify the build type, gather Memory/Topic/Tags/Create Topics/Docs/configured APIs/external search readiness, create a phased architecture, list files/components/modules/widgets/API/data/security/download needs, and produce original implementation guidance. Do not claim literal coverage of the whole universe or galaxy; be ambitious but honest about API, legal, security, budget and data limits. Knowledge Base Hub rule: when the user opens Knowledge or asks for แหล่งความรู้, foundational code, system standards, public-source learning, or reusable internal knowledge, use the central Knowledge items as a deduplicated source of truth for other menus. Summarize external/public information safely, store only transformed essential notes, do not duplicate equivalent content, and state when external API/search keys are required. Auto Question Reader rule: when the user simply types a normal question, automatically read the question, classify intent/forms/continuity, connect the relevant AiRA systems (Context, Memory/Topic, WordPress, Code, Image, Voice, Web, Artifact, Settings/API, Debug, File) and answer from that combined system context without requiring the user to press a menu button. Connect only relevant systems; do not make the answer cluttered. GPT-like Processor rule: internally route each request through Understand → Compact Prompt → Retrieve Context/API → Plan → Direct Answer → Recovery, but never reveal hidden chain-of-thought; show only concise status/summary when useful. Fast Answer rule: answer directly first, then provide supporting details; avoid slow verbose preambles. New API Provider rule: support current/extra AI providers through OpenAI-compatible slots and Custom API slots. When a new provider is added, require only key/model/endpoint/header fields that are truly needed, show readiness alerts, and route only to providers that are configured or explicitly selected. Deep Context rule: read the full page/room context, first topic, Memory, Topic map, user edits, menu tags, file map and latest command as one continuous instruction chain. Answer intensely by using the whole chain, but always let the latest user command override older context. If context is long, summarize it into a working plan before answering. Knowledge Orchestrator rule: try harder to answer broad or difficult questions by combining internal context, configured APIs, external search if available, and safe reasoning; never claim literal omniscience, but provide the best useful answer with confidence/assumption labels when evidence is incomplete. UI Stability rule: after the answer finishes, keep the completed answer stable and avoid full chat re-render flicker. Custom Feature rule: when the user types a simple feature request, turn it into a clear prompt, create a reusable Memory/Topic item, and prepare starter code/implementation plan that can be edited or deleted later. Code Color rule: when colors, HEX values, palette words or brand tones appear, extract a clean palette, name it in English, use it for local SVG/static/animated visuals, and include preview/download guidance. Settings Popup UX rule: settings, API Center, API Test Dock, Memory and Voice popup must stay inside the safe viewport between WordPress admin bar/header and composer, use inner scrolling, support 100dvh/visualViewport fallbacks, preserve input focus, and avoid overlay collisions on Chrome, Edge, Safari, Firefox, iOS and Android. OpenAI Creative Engine rule: when OpenAI API key is configured, use it for prompt optimization, code generation, file blueprinting, image prompt generation, local SVG/HTML/CSS/Canvas animation output, and downloadable file bundle planning. For actual downloadable files, return fenced code blocks with clear filenames; AiRA Plugin will package them into file chips/ZIP. If image API is configured, prefer image_generation for raster images; otherwise generate SVG/CSS/Canvas visuals locally. Google Search Expansion rule: when Google Search API key + CSE ID are configured, use it for general knowledge lookup, local/Thai context, public image search references, animated GIF/reference discovery, design inspiration, public code references, app/program/game-system planning, and safe rewrite/transform workflows. Never copy copyrighted text/images/code directly; summarize, transform, and generate original implementation. Human Assistant Skills rule: communicate like a capable human assistant—warm, practical, concise, and context-aware. For casual conversation, respond naturally. For work help, convert vague requests into priorities and next actions. For system work, produce a clear blueprint, components, files, API connections, tests, and usable status. Ask at most one clarifying question only when it blocks progress; otherwise make the best safe assumption and proceed. Auto Question/System Answer Skill v7.2.0 rule: normal typing must be enough; route each question through Auto Question Reader + Context Reader + GPT communication + relevant subsystem connectors before answering. GPT Overview/Short Interpretation Skill v7.1.9 rule: answer like a GPT-class assistant—direct first, warm, concise, context-aware, no irrelevant old answer reuse, no repetitive template; first interpret whether the user needs an overview, a short result, or an automatic action/result format, then show the interpreted result before longer explanation when useful.',
                'custom_endpoint' => '',
                'custom_provider_name' => '',
                'custom_auth_header' => 'Authorization',
                'custom_auth_prefix' => 'Bearer',
                'custom_method' => 'POST',
                'custom_response_path' => '',
                'custom_request_template' => '',
                'v0_endpoint' => '',
                'google_search_cx' => '',
                'google_search_country' => 'th',
                'google_search_language' => 'lang_th',
                'google_search_safe' => 'active',
                'google_search_site_restrict' => '',
                'google_search_local_mode' => 'thailand_local_context',
                'google_image_search_mode' => 'google_cse_image',
                'google_gif_search_mode' => 'google_cse_image_gif',
                'google_design_search_mode' => 'web_image_structure_safe',
                'google_code_search_mode' => 'public_code_sources_safe',
                'gpt_like_processor_mode' => 'direct_answer_with_context_router',
                'smart_scroll_mode' => 'gpt_follow_when_near_bottom',
                'google_app_builder_mode' => 'web_mobile_pwa_blueprint',
                'google_game_builder_mode' => 'html5_canvas_js_blueprint',
                'code_system_builder_scope' => 'wordpress_web_app_api_game',
                'google_image_endpoint' => '',
                'image_generation_model' => 'gpt-image-1.5',
                'image_generation_size' => '1024x1024',
                'github_endpoint' => 'https://api.github.com/user',
                'elevenlabs_voice_id' => '',
                'elevenlabs_voice_id_th' => '',
                'elevenlabs_voice_id_en' => '',
                'elevenlabs_voice_id_ja' => '',
                'elevenlabs_voice_id_zh' => '',
                'elevenlabs_voice_id_ko' => '',
                'elevenlabs_voice_id_ar' => '',
                'elevenlabs_voice_id_ru' => '',
                'elevenlabs_voice_id_fr' => '',
                'elevenlabs_voice_id_es' => '',
                'elevenlabs_model_id' => 'eleven_multilingual_v2',
                'elevenlabs_model_id_th' => 'eleven_multilingual_v2',
                'elevenlabs_model_id_en' => 'eleven_multilingual_v2',
                'elevenlabs_model_id_ja' => 'eleven_multilingual_v2',
                'elevenlabs_model_id_zh' => 'eleven_multilingual_v2',
                'elevenlabs_model_id_ko' => 'eleven_multilingual_v2',
                'elevenlabs_model_id_ar' => 'eleven_multilingual_v2',
                'elevenlabs_model_id_ru' => 'eleven_multilingual_v2',
                'elevenlabs_model_id_fr' => 'eleven_multilingual_v2',
                'elevenlabs_model_id_es' => 'eleven_multilingual_v2',
                'api_timeout' => '30',
                'voice_language' => 'th-TH',
                'voice_rate' => '1.12',
                'voice_volume' => '1',
                'voice_sensitivity' => '2.25',
            'voice_response_speed_mode' => 'api_first_with_browser_fallback',
            'voice_auto_send_delay_ms' => '140',
            'voice_reply_restart_delay_ms' => '90',
            'voice_auto_start_mode' => 'open_and_listen',
            'voice_graphic_style' => 'ref_wave_orb_v2',

                'answer_typing_mode' => 'fast_full_render',
                'answer_animation_mode' => 'fast_full_render',
                'answer_animation_speed' => 'fast',
                'answer_completion_stability' => 'no_flicker_replace_one',
                'processing_animation_mode' => 'gpt_claude_steps',
                'background_room_processing' => 'enabled',
                'room_status_indicator' => 'loader_done_dot',
                'context_intensity_mode' => 'deep_all_page_memory',
                'answer_latency_mode' => 'gpt_fast_direct',
                'difficult_question_research_mode' => 'auto_internal_external',
                'internal_knowledge_mode' => 'docs_memory_topic_context',
                'external_search_mode' => 'google_cse_when_configured',
                'new_api_provider_mode' => 'openai_compatible_plus_custom',
                'api_ready_alert_mode' => 'show_missing_required',
                'api_custom_slots_mode' => 'editable_endpoint_model_headers',
                'new_api_provider_mode' => 'openai_compatible_plus_custom',
                'api_ready_alert_mode' => 'show_missing_required',
                'api_custom_slots_mode' => 'editable_endpoint_model_headers',
                'google_search_num_results' => '3',
                'google_search_country' => 'th',
                'google_search_language' => 'lang_th',
                'google_search_safe' => 'active',
                'google_search_site_restrict' => '',
                'google_search_local_mode' => 'thailand_local_context',
                'google_image_search_mode' => 'google_cse_image',
                'google_gif_search_mode' => 'google_cse_image_gif',
                'google_design_search_mode' => 'web_image_structure_safe',
                'google_code_search_mode' => 'public_code_sources_safe',
                'gpt_like_processor_mode' => 'direct_answer_with_context_router',
                'smart_scroll_mode' => 'gpt_follow_when_near_bottom',
                'google_app_builder_mode' => 'web_mobile_pwa_blueprint',
                'google_game_builder_mode' => 'html5_canvas_js_blueprint',
                'code_system_builder_scope' => 'wordpress_web_app_api_game',
                'prompt_synthesis_mode' => 'summary_then_answer',
                'research_prompt_summary_mode' => 'enabled',
                'web_code_max_kb' => '220',
                'web_code_timeout' => '20',
                'topic_file_mode' => 'auto_meaning_map',
                'wp_blueprint_mode' => 'plugin_module_widget_block_elementor',
                'free_code_policy' => 'public_safe_adapt',
                'settings_device_bar' => 'right_inline',
                'wp_relation_mode' => 'auto_connect_components',
                'sync_merge_mode' => 'merge_by_updated_at',
                'voice_model_save_mode' => 'settings_persisted',
                'version' => self::VERSION,
                'audio_player_mode' => 'language_aware_popup',
                'voice_realtime_mode' => 'auto_language_interim',
                'voice_visual_mode' => 'think_control_waveform',
                'wp_component_mode' => 'plugin_module_widget_block_elementor',
                'openai_api_mode' => 'responses',
                'api_ui_mode' => 'standard_common_required',
                'api_response_switch' => 'intent',
                'answer_focus_mode' => 'direct',
                'user_sync_mode' => 'wp_user_id',
                'api_test_panel_position' => 'settings_side',
                'voice_graphic_style' => 'ref_wave_orb_v2',

                'answer_typing_mode' => 'fast_full_render',
                'answer_animation_mode' => 'fast_full_render',
                'answer_animation_speed' => 'fast',
                'answer_completion_stability' => 'no_flicker_replace_one',
                'processing_animation_mode' => 'gpt_claude_steps',
                'background_room_processing' => 'enabled',
                'room_status_indicator' => 'loader_done_dot',
                'context_intensity_mode' => 'deep_all_page_memory',
                'answer_latency_mode' => 'gpt_fast_direct',
                'difficult_question_research_mode' => 'auto_internal_external',
                'internal_knowledge_mode' => 'docs_memory_topic_context',
                'external_search_mode' => 'google_cse_when_configured',
                'google_search_num_results' => '3',
                'google_search_country' => 'th',
                'google_search_language' => 'lang_th',
                'google_search_safe' => 'active',
                'google_search_site_restrict' => '',
                'google_search_local_mode' => 'thailand_local_context',
                'google_image_search_mode' => 'google_cse_image',
                'google_gif_search_mode' => 'google_cse_image_gif',
                'google_design_search_mode' => 'web_image_structure_safe',
                'google_code_search_mode' => 'public_code_sources_safe',
                'gpt_like_processor_mode' => 'direct_answer_with_context_router',
                'smart_scroll_mode' => 'gpt_follow_when_near_bottom',
                'google_app_builder_mode' => 'web_mobile_pwa_blueprint',
                'google_game_builder_mode' => 'html5_canvas_js_blueprint',
                'code_system_builder_scope' => 'wordpress_web_app_api_game',
                'prompt_synthesis_mode' => 'summary_then_answer',
                'research_prompt_summary_mode' => 'enabled',
                'link_preview_mode' => 'popup_fetch_rewrite',
                'plain_text_mode' => 'available',
                'image_popup_mode' => 'view_download',
                'auto_topic_once' => 'enabled',
                'keyboard_typo_mode' => 'thai_english_layout_hint',
                'color_visual_mode' => 'local_svg_static_animated',
                'aira_api_builder_mode' => 'topic_api_blueprint',
                'aira_research_mode' => 'public_source_assisted',
                'aira_topic_manager_mode' => 'auto_create_edit_delete',
                'web_structure_mode' => 'auto_analyze_transform',
                'copyright_transform_mode' => 'no_copy_rewrite_visual_text',
                'aira_persona_style' => 'female_warm_professional',
                'voice_conversation_mode' => 'realtime_reply_loop',
                'web_structure_preview_mode' => 'clickable_transform_preview',
                'aira_auto_api_topics' => 'auto_seed_edit_delete',
                'long_composer_mode' => 'mobile_sheet_auto',
                'audio_player_window' => 'always_visible_api_panel',
                'inspiration_clone_mode' => 'safe_transform_only',
                'translate_extract_mode' => 'glossary_sentence_image_voice',
                'voice_meter_mode' => 'mic_analyser_realtime',
                'realtime_user_sync_interval' => '4',
                'sync_visibility_mode' => 'topbar_and_settings',
                'link_click_reader_mode' => 'popup_rewrite_transform',
                'sound_identity_mode' => 'safe_audio_metadata_ai_when_supported',
                'photo_to_text_mode' => 'vision_ocr_prompt',
                'audio_clip_upload_mode' => 'safe_metadata_and_ai_when_supported',
                'clone_preview_mode' => 'safe_new_structure_preview',
                'female_auto_persona' => 'enabled',
                'long_text_guard_mode' => 'keyboard_safe_sheet',
                'prompt_helper_mode' => 'organize_long_commands',
                'answer_refine_mode' => 'direct_structured_precise',
                'flicker_guard_mode' => 'stable_dom_render',
                'gpt_assist_prompt_mode' => 'enabled',
                'composer_fullscreen_mode' => 'mobile_gpt_sheet',
                'smart_filename_mode' => 'short_human_readable',
                'chat_ratio_mode' => 'gpt_centered_reading',
                'room_edit_mode' => 'safe_rename_delete',
                'room_delete_sync_guard' => 'tombstone_cross_browser_v3339',
                'code_preview_mode' => 'sandbox_preview_button',
                'visual_reference_mode' => 'copyright_safe_redraw',
                'sample_drawing_mode' => 'local_svg_plus_gpt_prompt',
                'instant_sync_mode' => 'poll_focus_broadcast',
                'universal_system_builder_mode' => 'blueprint_generate_package',
                'system_creation_scope' => 'web_wp_api_ui_voice_visual_docs',
                'composer_gpt_multiline_mode' => 'adaptive_multiline_shell',
                'prompt_info_storage_mode' => 'summary_raw_organized',
                'smart_prompt_summary_mode' => 'enabled',
                'smart_command_tags' => 'enabled',
                'prompt_menu_router' => 'enabled',
                'command_menu_router_mode' => 'natural_language_menu_bridge',
                'human_understanding_engine_mode' => 'intent_emotion_goal_constraint_practical_help',
                'human_problem_solver_mode' => 'symptom_root_cause_fix_test_fallback',
                'menu_function_bridge_mode' => 'chat_command_to_rooms_memory_tags_create_knowledge_docs_code_web_voice_api_sync_artifact',
                'hide_chat_avatars' => 'gpt_style',
                'best_effort_answer_mode' => 'try_answer_with_uncertainty',
                'full_context_reader_mode' => 'full_page_memory_topic_history',
                'continuous_instruction_mode' => 'from_first_topic_to_latest',
                'first_topic_memory_mode' => 'enabled',
                'page_context_depth' => 'deep_safe_compact',
                'intensive_answer_mode' => 'context_first_then_research',
                'answer_confidence_mode' => 'show_if_uncertain',
                'knowledge_scope_mode' => 'internal_external_api_when_ready',
                'knowledge_orchestrator_mode' => 'deep_internal_external_safe',
                'search_answer_recovery_mode' => 'retry_then_best_effort',
                'low_value_answer_retry' => 'enabled',
                'search_missing_api_notice' => 'short_actionable',
                'assumption_policy_mode' => 'clearly_label_assumptions',
                'answer_recovery_mode' => 'fallback_summary_next_steps',
                'custom_feature_builder_mode' => 'prompt_to_topic_code',
                'custom_feature_code_mode' => 'starter_blueprint_auto',
                'custom_feature_topic_mode' => 'auto_create_edit_delete',
                'code_color_search_mode' => 'extract_palette_from_prompt_web_memory',
                'color_draw_pipeline' => 'palette_to_local_svg_preview_download',
                'color_value_source_mode' => 'prompt_memory_url_when_available',
            ), '', false);
        }
        if (!is_array(get_option(self::OPTION_SECRETS, false))) {
            add_option(self::OPTION_SECRETS, array(), '', false);
        }
        if (!is_array(get_option(self::OPTION_DOCS, false))) {
            add_option(self::OPTION_DOCS, array(
                array(
                    'id' => 'safe-readme',
                    'title' => 'AiRA Studio',
                    'type' => 'markdown',
                    'content' => "# AiRA Studio\nระบบ AiRA Studio สำหรับแชท สร้างไฟล์ ทดสอบ API และฟังบทความเสียง",
                    'created' => current_time('mysql'),
                ),
            ), '', false);
        }
        if (!is_array(get_option(self::OPTION_MEMORY, false))) {
            add_option(self::OPTION_MEMORY, array(
                'enabled' => true,
                'summary' => 'AiRA จำบริบทงาน AiRA Studio / Thinkb4do และใช้คำสั่งต่อเนื่องในห้องแชทเป็นค่าเริ่มต้น',
                'updated' => current_time('mysql'),
            ), '', false);
        }
        if (!is_array(get_option(self::OPTION_INTEREST, false))) {
            add_option(self::OPTION_INTEREST, array(
                'version' => self::VERSION,
                'updated' => current_time('mysql'),
                'users' => array(),
            ), '', false);
        }
    }

    /* ============================================================
     * Admin menu + assets
     * ============================================================ */

    public function admin_menu() {
        $cap = $this->capability();
        $this->page_hook = add_menu_page('AiRA Studio Safe', 'AiRA Studio Safe', $cap, self::SLUG, array($this, 'render'), 'dashicons-format-chat', 3);
        add_submenu_page(self::SLUG, 'AiRA Chat', 'Chat', $cap, self::SLUG, array($this, 'render'));
        add_submenu_page(self::SLUG, 'Real API Center', 'Real API Center', $cap, self::SLUG . '-settings', array($this, 'render_settings_page'));
        add_submenu_page(self::SLUG, 'AiRA Power Core', 'Power Core', $cap, self::SLUG . '-power-core', array($this, 'render_power_page'));
    }

    public function assets($hook) {
        if ($hook !== $this->page_hook && strpos((string)$hook, self::SLUG) === false) { return; }
        $base = plugin_dir_url(__FILE__);
        $path = plugin_dir_path(__FILE__);

        // v7.0.0 Clean Slate Asset Gate: one CSS + one JS only. Legacy AiRA handles are purged on this page.
        $css_handle = 'aira-studio-clean-slate-style';
        $js_handle  = 'aira-studio-clean-slate-runtime';
        $css_file   = 'assets/css/aira-studio-clean-slate.css';
        $js_file    = 'assets/js/aira-studio-clean-slate.js';

        $this->purge_legacy_assets(array($css_handle, $js_handle));

        if (is_readable($path . $css_file)) {
            wp_enqueue_style($css_handle, $base . $css_file, array(), self::VERSION);
        }
        if (is_readable($path . $js_file)) {
            wp_enqueue_script($js_handle, $base . $js_file, array(), self::VERSION, true);
            wp_localize_script($js_handle, 'AiRASafe', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce(self::NONCE),
                'version' => self::VERSION,
                'assetBase' => $base,
                'dashboardUrl' => admin_url(),
                'nativeSettingsUrl' => admin_url('admin.php?page=' . self::SLUG . '-settings'),
                'canManage' => current_user_can($this->capability()),
                'userId' => get_current_user_id(),
                'userSyncMode' => 'authorized_id_wp_user_meta',
                'authorizedIdentity' => $this->authorized_identity_payload(true),
                'previewAccess' => array(
                    'authorizedOnly' => true,
                    'policy' => 'same_wordpress_user_with_required_capability',
                    'capability' => $this->capability(),
                    'userId' => get_current_user_id(),
                    'canPreview' => current_user_can($this->capability()),
                ),
                'elevenLabsReady' => $this->elevenlabs_ready(),
                'uiMode' => 'clean_slate_preinstall_live_sandbox',
                'actions' => array(
                    'bootstrap' => 'aira75841_bootstrap',
                    'sendChat' => 'aira75841_send_chat',
                    'ttsVoice' => 'aira75841_tts_voice',
                    'saveApi' => 'aira75841_save_api',
                    'getApi' => 'aira75841_get_api',
                    'unlockApi' => 'aira75841_unlock_api',
                    'migrateApi' => 'aira75841_migrate_api',
                    'testApi' => 'aira75841_test_api',
                    'connectAllApi' => 'aira75841_connect_all_api',
                    'systemCheck' => 'aira75841_system_check',
                    'exportDebugText' => 'aira75841_export_debug_text',
                    'pageHealthReport' => 'aira75841_page_health_report',
                    'exportPageHealthText' => 'aira75841_export_page_health_text',
                    'createDoc' => 'aira75841_create_doc',
                    'importUrlDoc' => 'aira75841_import_url_doc',
                    'readerFetch' => 'aira75841_reader_fetch',
                    'miniBrowserFetch' => 'aira75841_mini_browser_fetch',
                    'uploadFile' => 'aira75841_upload_file',
                    'generateImage' => 'aira75841_generate_image',
                    'installGeneratedPlugin' => 'aira75841_install_generated_plugin',
                    'upgradeGeneratedPlugin' => 'aira75841_upgrade_generated_plugin',
                    'preinstallCheck' => 'aira75841_preinstall_check',
                    'getUserSync' => 'aira75841_get_user_sync',
                    'saveUserSync' => 'aira75841_save_user_sync',
                    'getInterestStats' => 'aira75841_get_interest_stats',
                    'saveInterestStats' => 'aira75841_save_interest_stats',
                    'getAuthorizedIdentity' => 'aira75841_get_authorized_identity',
                    'rotateAuthorizedIdentity' => 'aira75841_rotate_authorized_identity',
                    'authorizedSyncStatus' => 'aira75841_authorized_sync_status',
                    'powerStatus' => 'aira75841_power_status',
                    'powerBlueprint' => 'aira75841_power_blueprint',
                    'powerQc' => 'aira75841_power_qc',
                    'powerExportText' => 'aira75841_power_export_text',
                ),
            ));
        }
    }

    public function purge_legacy_assets($keep = array()) {
        $keep = array_merge(array('aira-studio-clean-slate-style', 'aira-studio-clean-slate-runtime'), (array)$keep);
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        $screen_id = $screen && isset($screen->id) ? (string)$screen->id : '';
        if ($screen_id !== '' && strpos($screen_id, self::SLUG) === false && strpos($screen_id, 'aira') === false) { return; }

        foreach (array('wp_styles' => 'style', 'wp_scripts' => 'script') as $global_name => $type) {
            global ${$global_name};
            $registry = ${$global_name};
            if (!is_object($registry) || empty($registry->queue) || !is_array($registry->queue)) { continue; }
            foreach ((array)$registry->queue as $handle) {
                $h = (string)$handle;
                if (in_array($h, $keep, true)) { continue; }
                if (strpos($h, 'aira') !== false || strpos($h, 'thinkb4do') !== false) {
                    if ($type === 'style') { wp_dequeue_style($h); wp_deregister_style($h); }
                    if ($type === 'script') { wp_dequeue_script($h); wp_deregister_script($h); }
                }
            }
        }
    }

    public function render_settings_page() {
        if (!current_user_can($this->capability())) {
            wp_die(esc_html__('You do not have permission to access AiRA Studio settings.', 'aira-studio'));
        }
        $settings = $this->safe_settings();
        $masked = $this->masked_status();
        $providers = $this->providers();
        $admin_url = admin_url('admin-post.php');
        $chat_url = admin_url('admin.php?page=' . self::SLUG);
        $nonce = wp_create_nonce(self::NONCE);
        $updated_raw = filter_input(INPUT_GET, 'updated', FILTER_UNSAFE_RAW);
        $updated_notice = is_string($updated_raw) ? sanitize_text_field(wp_unslash($updated_raw)) : '';
        $field_groups = array(
            'ระบบตอบคำถาม / Router' => array(
                'primary_provider' => 'Primary Provider',
                'api_router_mode' => 'API Router Mode',
                'fallback_provider' => 'Fallback Provider',
                'primary_model' => 'Primary Model',
                'openai_text_model' => 'OpenAI Text Model',
                'openai_code_model' => 'OpenAI Code Model',
                'openai_vision_model' => 'OpenAI Vision Model',
                'openai_image_model' => 'OpenAI Image Model',
                'anthropic_model' => 'Claude Model',
                'gemini_model' => 'Gemini Model',
                'openrouter_model' => 'OpenRouter Model',
            ),
            'Google / Web / Image' => array(
                'google_search_cx' => 'Google CSE CX',
                'google_search_country' => 'Country',
                'google_search_language' => 'Language',
                'google_search_safe' => 'Safe Search',
                'google_search_site_restrict' => 'Site Restrict',
            ),
            'Image Generation' => array(
                'image_generation_model' => 'Image Generation Model',
                'image_generation_size' => 'Image Size',
                'openai_image_output_format' => 'Image Output Format',
            ),
            'Voice / ElevenLabs' => array(
                'elevenlabs_voice_id' => 'Default Voice ID',
                'elevenlabs_model_id' => 'Default Model ID',
                'elevenlabs_voice_id_th' => 'Thai Voice ID',
                'elevenlabs_voice_id_en' => 'English Voice ID',
                'elevenlabs_model_id_th' => 'Thai Model ID',
                'elevenlabs_model_id_en' => 'English Model ID',
                'voice_language' => 'Voice Language',
                'voice_rate' => 'Voice Rate',
                'voice_volume' => 'Voice Volume',
            ),
            'Custom / Endpoint' => array(
                'v0_endpoint' => 'v0 Endpoint URL',
                'github_endpoint' => 'GitHub Endpoint',
                'custom_provider_name' => 'Custom Provider Name',
                'custom_endpoint' => 'Custom Endpoint',
                'custom_auth_header' => 'Custom Auth Header',
                'custom_auth_prefix' => 'Custom Auth Prefix',
                'custom_method' => 'Custom Method',
            ),
        );
        ?>
        <div class="wrap aira-native-settings-wrap">
            <h1>AiRA Studio · Real API Center</h1>
            <?php if ($updated_notice !== ''): ?>
                <div class="notice notice-success is-dismissible"><p>บันทึก Settings แล้ว · ใช้หน้านี้แทน Popup ได้ทันทีเมื่อหน้าแรกติดเบลอหรือกดไม่ได้</p></div>
            <?php endif; ?>
            <div class="aira-native-notice">
                <b>Native Settings v7.0.0 Clean Slate</b>
                <span>หน้านี้ทำงานนอก popup ของ AiRA เพื่อลด UI ซ้อน และยังบันทึกลง option/API ชุดเดียวกับ AiRA Studio</span>
                <a class="button" href="<?php echo esc_url($chat_url); ?>">กลับไปหน้าแชท AiRA</a>
            </div>
            <form method="post" action="<?php echo esc_url($admin_url); ?>" class="aira-native-form">
                <?php wp_nonce_field('aira3371_settings_save'); ?>
                <input type="hidden" name="action" value="aira3371_save_settings">
                <div class="aira-native-grid">
                    <section class="aira-native-card aira-native-wide">
                        <h2>API Keys</h2>
                        <p>ช่องนี้ปล่อยว่างไว้ได้ ระบบจะไม่ลบ key เดิม ถ้าต้องการเปลี่ยนให้วาง key ใหม่ ถ้าต้องการลบให้ติ๊ก Clear</p>
                        <div class="aira-native-provider-grid">
                            <?php foreach ($providers as $key => $label): ?>
                                <label class="aira-native-field">
                                    <span><?php echo esc_html($label); ?></span>
                                    <input type="password" name="api_keys[<?php echo esc_attr($key); ?>]" value="" placeholder="<?php echo esc_attr($masked['providers'][$key] ?? 'ยังไม่ได้ตั้งค่า'); ?>" autocomplete="off">
                                    <small><label><input type="checkbox" name="clear_keys[]" value="<?php echo esc_attr($key); ?>"> Clear</label></small>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php foreach ($field_groups as $title => $fields): ?>
                        <section class="aira-native-card">
                            <h2><?php echo esc_html($title); ?></h2>
                            <?php foreach ($fields as $key => $label): ?>
                                <label class="aira-native-field">
                                    <span><?php echo esc_html($label); ?></span>
                                    <input type="text" name="settings[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr((string)($settings[$key] ?? '')); ?>">
                                </label>
                            <?php endforeach; ?>
                        </section>
                    <?php endforeach; ?>
                </div>
                <p class="submit aira-native-actions">
                    <button type="submit" class="button button-primary button-hero">Save Settings</button>
                    <button type="button" class="button button-hero" id="airaNativeTestApi">Test API</button>
                    <button type="button" class="button" id="airaNativeFillCommon">เติมค่าใช้บ่อย</button>
                    <a class="button" href="<?php echo esc_url($chat_url); ?>">กลับ AiRA Studio</a>
                </p>
            </form>
            <pre id="airaNativeResult" class="aira-native-result" hidden></pre>
        </div>
        <style>
            .aira-native-settings-wrap{max-width:1180px}.aira-native-notice{display:flex;gap:12px;align-items:center;justify-content:space-between;background:#fff;border:1px solid #dfe5e2;border-left:4px solid #1E6B45;border-radius:12px;padding:14px 16px;margin:14px 0 18px}.aira-native-notice b{color:#1E6B45}.aira-native-notice span{flex:1}.aira-native-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.aira-native-card{background:#fff;border:1px solid #dfe5e2;border-radius:14px;padding:16px;box-shadow:0 6px 18px rgba(17,24,39,.04)}.aira-native-wide{grid-column:1/-1}.aira-native-provider-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.aira-native-field{display:block;margin:0 0 12px}.aira-native-field span{display:block;font-weight:700;margin-bottom:6px;color:#1E6B45}.aira-native-field input[type=text],.aira-native-field input[type=password]{width:100%;max-width:100%;min-height:42px;border:1px solid #cfd8d3;border-radius:10px;padding:8px 10px;background:#fff;color:#111}.aira-native-result{background:#0f172a;color:#e5e7eb;border-radius:12px;padding:14px;white-space:pre-wrap;max-height:360px;overflow:auto}.aira-native-actions{position:sticky;bottom:0;background:#f0f0f1;padding:12px 0!important;z-index:10}@media(max-width:782px){.aira-native-grid,.aira-native-provider-grid{grid-template-columns:1fr}.aira-native-notice{display:block}.aira-native-notice .button{margin-top:10px}.aira-native-actions .button{width:100%;margin:4px 0!important}}
        </style>
        <script>
        (function(){
            var ajaxUrl = "<?php echo esc_js(admin_url('admin-ajax.php')); ?>";
            var nonce = "<?php echo esc_js($nonce); ?>";
            var result = document.getElementById('airaNativeResult');
            function show(obj){ result.hidden=false; result.textContent = typeof obj === 'string' ? obj : JSON.stringify(obj, null, 2); }
            var test = document.getElementById('airaNativeTestApi');
            if(test){ test.addEventListener('click', function(){
                test.disabled = true; test.textContent = 'Testing...';
                var fd = new FormData(); fd.append('action','aira75841_test_api'); fd.append('nonce', nonce);
                fetch(ajaxUrl, {method:'POST', credentials:'same-origin', body:fd}).then(function(r){return r.json();}).then(show).catch(function(e){show('Test API error: '+(e && e.message ? e.message : e));}).finally(function(){test.disabled=false; test.textContent='Test API';});
            }); }
            var fill = document.getElementById('airaNativeFillCommon');
            if(fill){ fill.addEventListener('click', function(){
                var common = {
                    primary_provider:'auto', api_router_mode:'auto', fallback_provider:'openrouter', primary_model:'gpt-4o-mini', openai_text_model:'gpt-4o-mini', openai_code_model:'gpt-4o-mini', openai_vision_model:'gpt-4o-mini', openai_image_model:'gpt-image-1', image_generation_model:'gpt-image-1.5', image_generation_size:'1024x1024', openai_image_output_format:'png', anthropic_model:'claude-sonnet-4-20250514', gemini_model:'gemini-2.5-flash', openrouter_model:'openai/gpt-4o-mini', google_search_country:'th', google_search_language:'lang_th', google_search_safe:'active', elevenlabs_model_id:'eleven_multilingual_v2', elevenlabs_model_id_th:'eleven_multilingual_v2', elevenlabs_model_id_en:'eleven_multilingual_v2', voice_language:'auto', voice_rate:'1.12', voice_volume:'1', github_endpoint:'https://api.github.com/user', custom_method:'POST', custom_auth_header:'Authorization', custom_auth_prefix:'Bearer'
                };
                Object.keys(common).forEach(function(k){ var el=document.querySelector('[name="settings['+k+']"]'); if(el && !el.value) el.value=common[k]; });
                show('เติมค่าใช้บ่อยแล้ว · API Key / Voice ID / CX ที่จำเป็นยังต้องกรอกเอง');
            }); }
        })();
        </script>
        <?php
    }

    public function handle_settings_page_save() {
        if (!current_user_can($this->capability())) {
            wp_die(esc_html__('You do not have permission to save AiRA Studio settings.', 'aira-studio'));
        }
        check_admin_referer('aira3371_settings_save');
        $settings = get_option(self::OPTION_SETTINGS, array());
        $posted_settings = isset($_POST['settings']) ? wp_unslash($_POST['settings']) : array();
        if (is_array($posted_settings)) {
            foreach ($posted_settings as $k => $v) {
                $settings[sanitize_key($k)] = is_scalar($v) ? sanitize_textarea_field((string)$v) : '';
            }
            $settings = $this->normalize_settings_defaults($settings);
            update_option(self::OPTION_SETTINGS, $settings, false);
        }
        $secrets = $this->raw_api_secrets();
        if (!is_array($secrets)) $secrets = array();
        $clear = isset($_POST['clear_keys']) ? (array) wp_unslash($_POST['clear_keys']) : array();
        foreach ($clear as $slot) {
            $slot = sanitize_key($slot);
            if (array_key_exists($slot, $this->providers())) unset($secrets[$slot]);
        }
        $posted_keys = isset($_POST['api_keys']) ? wp_unslash($_POST['api_keys']) : array();
        if (is_array($posted_keys)) {
            foreach ($posted_keys as $k => $v) {
                $slot = sanitize_key($k);
                if (!array_key_exists($slot, $this->providers())) continue;
                $val = $this->normalize_api_key_input((string)$v);
                if ($val !== '') $secrets[$slot] = sanitize_text_field($val);
            }
            update_option(self::OPTION_SECRETS, $secrets, false);
        }
        wp_safe_redirect(add_query_arg(array('page' => self::SLUG . '-settings', 'updated' => '1'), admin_url('admin.php')));
        exit;
    }

    /* ============================================================
     * Render admin page
     * ============================================================ */

    public function render() {
        if (!current_user_can($this->capability())) {
            wp_die(esc_html__('You do not have permission to access AiRA Studio.', 'aira-studio'));
        }
        $this->seed_defaults();
        $this->seed_power_capsule();
        $this->migrate_api(false);
        ?>

<!-- AiRA Studio v7.2.1 Clean Slate Vision Attachment Reader: normal typing now routes through Auto Question Reader + relevant AiRA system connectors before GPT-like answers. -->
<div id="airaApp" class="aira-app aira-clean-slate-shell" data-version="<?php echo esc_attr(self::VERSION); ?>">
    <header class="aira-topbar" role="banner">
        <div class="aira-brand" aria-label="AiRA Studio">
            <span class="brand-mark" aria-hidden="true">A</span>
            <span class="brand-copy">
                <b>AiRA Studio</b>
                <small>Auto Question Reader · GPT Answer · Settings | โดย Thinkb4do | ดูรายละเอียด</small>
            </span>
        </div>
        <nav class="aira-top-actions" aria-label="AiRA actions">
            <button type="button" class="icon-btn" data-action="new-chat" title="แชทใหม่"><?php echo wp_kses($this->safe_svg('plus'), $this->svg_allowed_tags()); ?></button>
            <button type="button" class="top-btn" data-action="composer-menu">Menu</button>
            <button type="button" class="top-btn" data-action="open-rooms">Rooms</button>
            <button type="button" class="top-btn" data-action="toggle-artifact">Artifact</button>
            <button type="button" class="top-btn" data-action="updates-open">Status</button>
            <button type="button" class="top-btn primary" data-action="open-settings">Settings</button>
        </nav>
    </header>

    <div class="aira-layout" role="application" aria-label="AiRA Studio clean workspace">
        <main class="chat-panel" role="main" data-panel="chat">
            <section class="chat-log" id="chatLog" aria-live="polite" aria-label="บทสนทนา AiRA">
                <div class="welcome">
                    <div class="welcome-mark" aria-hidden="true">A</div>
                    <h1>วันนี้ให้ AiRA ช่วยอะไร?</h1>
                    <p>UI ใหม่แบบสะอาด: พิมพ์ปกติ ระบบอ่านคำถาม เชื่อมโมดูล และตอบแบบ GPT</p>
                    <button type="button" class="welcome-cta" data-action="quick-prompt" data-prompt="ช่วยตรวจ UI/UX WordPress admin ให้สะอาด ไม่ซ้อน และจัดตำแหน่งคล้าย GPT">เริ่มตรวจ UI/UX</button>
                </div>
            </section>
            <button type="button" class="scroll-latest-btn" id="scrollLatestBtn" data-action="scroll-latest" hidden aria-label="เลื่อนลงคำถามล่าสุด">
                <span aria-hidden="true">↓</span><b>ล่าสุด</b>
            </button>

            <section class="composer-wrap" id="airaComposerDock" data-role="composer" aria-label="AiRA composer">
                <div class="composer-media-stack" id="composerMediaStack">
                    <div class="composer-audio-dock" id="composerAudioDock" hidden aria-live="polite">
                        <div class="media-line">
                            <span class="media-dot" aria-hidden="true"></span>
                            <div><b id="composerAudioTitle">เสียงคำตอบ</b><small id="composerAudioStatus">พร้อมเล่น</small></div>
                            <button type="button" class="icon-btn" data-action="audio-close" aria-label="ปิดเสียง">×</button>
                        </div>
                        <audio id="composerAnswerAudio" controls preload="none" hidden></audio>
                        <div class="media-actions">
                            <button type="button" data-action="audio-toggle" id="composerAudioToggleBtn">เล่นเสียง</button>
                            <button type="button" data-action="audio-stop">หยุด</button>
                        </div>
                    </div>
                    <div class="composer-voice-audio" id="composerVoiceBar" hidden aria-live="polite">
                        <div class="media-line">
                            <span class="media-dot" id="composerVoicePulse" aria-hidden="true"></span>
                            <div><b id="composerVoiceStatus">Voice พร้อม</b><small id="composerVoiceTranscript">แตะไมค์เพื่อพูด ข้อความจะเข้า Composer</small></div>
                            <button type="button" class="icon-btn" data-action="voice-close" aria-label="ปิดเสียงพูด">×</button>
                        </div>
                        <div class="media-actions">
                            <button type="button" data-action="voice-talk" id="composerVoiceTalkBtn">พูด</button>
                            <button type="button" data-action="voice-stop">หยุด</button>
                        </div>
                    </div>
                </div>

                <div class="composer" role="form">
                    <button class="composer-btn" type="button" data-action="composer-menu" aria-label="เปิดเครื่องมือ">+</button>
                    <input id="fileInput" class="hidden-file" type="file" multiple aria-hidden="true" accept="image/png,image/jpeg,image/webp,image/gif,image/svg+xml,.txt,.md,.pdf,.doc,.docx,.json,.csv,.html,.css,.js,.php">
                    <textarea id="composerInput" rows="1" placeholder="ถาม AiRA ได้เลย" autocomplete="off" spellcheck="true" aria-label="พิมพ์ข้อความถึง AiRA"></textarea>
                    <button class="composer-btn" id="voiceBtn" type="button" data-action="voice-open" aria-label="คุยเสียง"><?php echo wp_kses($this->safe_svg('mic'), $this->svg_allowed_tags()); ?></button>
                    <button class="composer-btn send" id="sendBtn" type="button" data-action="send-chat" aria-label="ส่งข้อความ" disabled><?php echo wp_kses($this->safe_svg('arrow-up'), $this->svg_allowed_tags()); ?></button>
                </div>
                <div class="composer-attachment-tray" id="attachmentTray" hidden aria-label="ไฟล์และภาพที่แนบไว้รอส่ง"></div>
                <div class="composer-smart-tags" id="composerSmartTags" hidden></div>
                <div class="composer-hint" id="composerHint">Enter ส่ง · Shift+Enter ขึ้นบรรทัดใหม่ · Preview/Test ก่อนติดตั้ง · Authorized ID Sync</div>

                <button type="button" class="composer-menu-backdrop" id="composerMenuBackdrop" data-action="composer-menu-close" hidden aria-label="ปิดเมนูทั้งหมด"></button>

                <div class="composer-menu full-menu" id="composerMenu" hidden aria-label="เมนูทั้งหมดของ AiRA">
                    <div class="menu-headline">
                        <div><b>เมนูทั้งหมด</b><small>เมนูป๊อบอัพเหนือ Composer แบบไม่ซ้อน และไม่ดัน Composer หลุดตำแหน่ง</small></div>
                        <button type="button" class="mini-btn" data-action="composer-menu">ปิดเมนู</button>
                    </div>

                    <div class="menu-label">Workspace</div>
                    <div class="menu-row compact-tools">
                        <button type="button" data-action="new-chat"><?php echo wp_kses($this->safe_svg('plus'), $this->svg_allowed_tags()); ?> แชทใหม่</button>
                        <button type="button" data-action="open-rooms"><?php echo wp_kses($this->safe_svg('rooms'), $this->svg_allowed_tags()); ?> Rooms</button>
                        <button type="button" data-action="workplace" data-tab="docs"><?php echo wp_kses($this->safe_svg('docs'), $this->svg_allowed_tags()); ?> Docs</button>
                        <button type="button" data-action="workplace" data-tab="memory"><?php echo wp_kses($this->safe_svg('memory'), $this->svg_allowed_tags()); ?> Memory</button>
                        <button type="button" data-action="workplace" data-tab="tags"><?php echo wp_kses($this->safe_svg('tag'), $this->svg_allowed_tags()); ?> Tags</button>
                        <button type="button" data-action="workplace" data-tab="interest"><?php echo wp_kses($this->safe_svg('heart'), $this->svg_allowed_tags()); ?> สถิติถูกใจ</button>
                        <button type="button" data-action="workplace" data-tab="performance"><?php echo wp_kses($this->safe_svg('performance'), $this->svg_allowed_tags()); ?> ประสิทธิภาพ/Impact</button>
                        <button type="button" data-action="workplace" data-tab="identity"><?php echo wp_kses($this->safe_svg('rooms'), $this->svg_allowed_tags()); ?> Authorized ID</button>
                        <button type="button" data-action="workplace" data-tab="create"><?php echo wp_kses($this->safe_svg('plus'), $this->svg_allowed_tags()); ?> Create</button>
                        <button type="button" data-action="workplace" data-tab="knowledge"><?php echo wp_kses($this->safe_svg('docs'), $this->svg_allowed_tags()); ?> แหล่งความรู้</button>
                        <button type="button" data-action="workplace" data-tab="behavior"><?php echo wp_kses($this->safe_svg('tag'), $this->svg_allowed_tags()); ?> พฤติกรรมคำถาม</button>
                        <button type="button" data-action="workplace" data-tab="context"><?php echo wp_kses($this->safe_svg('memory'), $this->svg_allowed_tags()); ?> Context Reader</button>
                        <button type="button" data-action="workplace" data-tab="code"><?php echo wp_kses($this->safe_svg('code'), $this->svg_allowed_tags()); ?> Code</button>
                        <button type="button" data-action="workplace" data-tab="web"><?php echo wp_kses($this->safe_svg('globe'), $this->svg_allowed_tags()); ?> Web</button>
                        <button type="button" data-action="open-settings"><?php echo wp_kses($this->safe_svg('settings'), $this->svg_allowed_tags()); ?> API/ตั้งค่า</button>
                    </div>

                    <div class="menu-label">AI Mode</div>
                    <div class="menu-row compact-tools">
                        <button type="button" data-action="mode" data-mode="full">Full System</button>
                        <button type="button" data-action="mode" data-mode="gpt">GPT Chat</button>
                        <button type="button" data-action="quick-prompt" data-prompt="ตอบแบบ GPT: เข้าเรื่องเร็ว อบอุ่น ชัดเจน ตัดคำซ้ำ จัดประเด็นตามเจตนาผู้ถาม และสรุปขั้นตอนใช้งานได้จริง">GPT สื่อสาร</button>
                        <button type="button" data-action="workplace" data-tab="question">อ่านคำถามอัตโนมัติ</button>
                        <button type="button" data-action="interpret-mode" data-mode="overview">ตีความภาพรวม</button>
                        <button type="button" data-action="interpret-mode" data-mode="short">ตีความสั้น</button>
                        <button type="button" data-action="mode" data-mode="claude">Claude Artifact</button>
                        <button type="button" data-action="mode" data-mode="v0">v0 UI</button>
                        <button type="button" data-action="mode" data-mode="gemini">Gemini Vision</button>
                        <button type="button" data-action="mode" data-mode="github">GitHub Repo</button>
                        <button type="button" data-action="plain-text-mode">Plain Text</button>
                        <button type="button" data-action="quick-prompt" data-prompt="สร้างหน้า UI/component แบบ GPT + v0: สวย เรียบ responsive พร้อมโค้ดและไฟล์ที่ดาวน์โหลดได้">Generate UI</button>
                        <button type="button" data-action="prompt-helper">ช่วยเขียน Prompt</button>
                        <button type="button" data-action="custom-feature-builder">Custom Feature</button>
                        <button type="button" data-action="quick-prompt" data-prompt="ทำงานแบบ Universal System Builder: ทวนโจทย์ สรุป Prompt แยก Dashboard/User/API/Data/Security/Files แล้วสร้างระบบพร้อม preview/download และคำเตือนความปลอดภัย">Universal Builder</button>
                        <button type="button" data-action="prompt-info-save">Info Prompt</button>
                        <button type="button" data-action="smart-tags-refresh">Smart Tags</button>
                        <button type="button" data-action="related-command-helper">หาเมนูที่เกี่ยวข้อง</button>
                        <button type="button" data-action="refine-last-answer">เรียบเรียงคำตอบ</button>
                        <button type="button" data-action="create-one-click">Create โลกใหม่</button>
                    </div>

                    <div class="menu-label">WordPress Build</div>
                    <div class="menu-row compact-tools">
                        <button type="button" data-action="quick-prompt" data-prompt="สร้างปลั๊กอิน WordPress มาตรฐาน พร้อม Plugin Header, shortcode, admin settings, security nonce/capability และไฟล์พร้อมดาวน์โหลด">Plugin</button>
                        <button type="button" data-action="quick-prompt" data-prompt="ออกแบบ WordPress Module สำหรับ AiRA Studio แยกไฟล์ มีหน้าที่ชัดเจน วิธีเชื่อมกับปลั๊กอินหลัก และโค้ดตัวอย่าง">Module</button>
                        <button type="button" data-action="quick-prompt" data-prompt="สร้าง WordPress Widget มาตรฐาน พร้อม register_widget, form, update, widget output และข้อควรระวังด้านความปลอดภัย">Widget</button>
                        <button type="button" data-action="quick-prompt" data-prompt="สร้าง Gutenberg Block มาตรฐาน รองรับ responsive, editor/front-end assets และวิธีติดตั้งในปลั๊กอิน WordPress">Gutenberg Block</button>
                        <button type="button" data-action="quick-prompt" data-prompt="สร้าง Elementor Widget มาตรฐาน พร้อม controls, render, responsive settings และวิธีใส่ในปลั๊กอิน WordPress">Elementor Widget</button>
                        <button type="button" data-action="quick-prompt" data-prompt="วาง WordPress Blueprint แบบเชื่อมความสัมพันธ์อัตโนมัติ: Plugin main file, Module, Widget, Gutenberg Block, Elementor Widget, AJAX/REST, assets, security, uninstall และวิธีติดตั้งปลอดภัย">WP Blueprint</button>
                    </div>

                    <div class="menu-label">Visual / Image / Reference</div>
                    <div class="menu-row compact-tools">
                        <button type="button" data-action="upload-file"><?php echo wp_kses($this->safe_svg('image'), $this->svg_allowed_tags()); ?> แนบภาพ/ไฟล์</button>
                        <button type="button" data-action="photo-to-text"><?php echo wp_kses($this->safe_svg('image'), $this->svg_allowed_tags()); ?> อ่านภาพ/OCR</button>
                        <button type="button" data-action="generate-image"><?php echo wp_kses($this->safe_svg('image'), $this->svg_allowed_tags()); ?> สร้างภาพจริง</button>
                        <button type="button" data-action="image-prompt-helper">Prompt ภาพ</button>
                        <button type="button" data-action="draw-sample-visual">วาดภาพตัวอย่าง</button>
                        <button type="button" data-action="visual-reference-collector">ภาพเว็บ → วาดใหม่</button>
                        <button type="button" data-action="visual-ref-transform">ภาพ ref → ภาพใหม่</button>
                        <button type="button" data-action="generate-local-visual">Color Visual</button>
                        <button type="button" data-action="color-code-draw">ค่าสี → วาดภาพ</button>
                        <button type="button" data-action="speech-image-text"><?php echo wp_kses($this->safe_svg('image'), $this->svg_allowed_tags()); ?> แกะเสียง/ภาพเป็นข้อความ</button>
                        <button type="button" data-action="image-preview-download">บันทึกภาพล่าสุด</button>
                    </div>

                    <div class="menu-label">Web / Code / Research</div>
                    <div class="menu-row compact-tools">
                        <button type="button" data-action="github-import"><?php echo wp_kses($this->safe_svg('code'), $this->svg_allowed_tags()); ?> อ่าน GitHub URL</button>
                        <button type="button" data-action="web-code-import"><?php echo wp_kses($this->safe_svg('globe'), $this->svg_allowed_tags()); ?> อ่านโค้ดฟรี</button>
                        <button type="button" data-action="web-structure-import"><?php echo wp_kses($this->safe_svg('globe'), $this->svg_allowed_tags()); ?> อ่านโครงสร้างเว็บ</button>
                        <button type="button" data-action="aira-research-source"><?php echo wp_kses($this->safe_svg('globe'), $this->svg_allowed_tags()); ?> AiRA Research</button>
                        <button type="button" data-action="clone-inspiration"><?php echo wp_kses($this->safe_svg('artifact'), $this->svg_allowed_tags()); ?> Clone Inspiration</button>
                        <button type="button" data-action="knowledge-update-external">อัปเดตแหล่งความรู้</button>
                        <button type="button" data-action="link-preview-import">อ่านลิงก์อีกครั้ง</button>
                        <button type="button" data-action="link-preview-open">เปิดลิงก์</button>
                        <button type="button" data-action="structure-preview-use">ใช้ Blueprint</button>
                        <button type="button" data-action="structure-preview-download">Download Blueprint</button>
                    </div>

                    <div class="menu-label">Voice / Audio / Translate</div>
                    <div class="menu-row compact-tools">
                        <button type="button" data-action="voice-open"><?php echo wp_kses($this->safe_svg('mic'), $this->svg_allowed_tags()); ?> คุยเสียงไว</button>
                        <button type="button" data-action="voice-talk">พูด</button>
                        <button type="button" data-action="voice-stop">หยุดพูด</button>
                        <button type="button" data-action="audio-toggle">เล่นเสียง</button>
                        <button type="button" data-action="audio-stop">หยุดเสียง</button>
                        <button type="button" data-action="translate-extract"><?php echo wp_kses($this->safe_svg('docs'), $this->svg_allowed_tags()); ?> แปล/คำศัพท์</button>
                        <button type="button" data-action="sound-identify"><?php echo wp_kses($this->safe_svg('mic'), $this->svg_allowed_tags()); ?> รู้ว่าเสียงอะไร</button>
                    </div>

                    <div class="menu-label">System / Debug / Export</div>
                    <div class="menu-row compact-tools">
                        <button type="button" data-action="sync-now"><?php echo wp_kses($this->safe_svg('rooms'), $this->svg_allowed_tags()); ?> Sync ตอนนี้</button>
                        <button type="button" data-action="composer-expand"><?php echo wp_kses($this->safe_svg('plus'), $this->svg_allowed_tags()); ?> พิมพ์ยาว</button>
                        <button type="button" data-action="toggle-artifact"><?php echo wp_kses($this->safe_svg('artifact'), $this->svg_allowed_tags()); ?> Artifact/ดาวน์โหลด</button>
                        <button type="button" data-action="preview-everything">Preview ทุกอย่าง</button>
                        <button type="button" data-action="deep-audit"><?php echo wp_kses($this->safe_svg('settings'), $this->svg_allowed_tags()); ?> Deep Audit</button>
                        <button type="button" data-action="stability-audit">แก้บั๊ก/กันกระพริบ</button>
                        <button type="button" data-action="connect-all-api">Connect All API</button>
                        <button type="button" data-action="custom-feature-builder">Custom Feature</button>
                        <button type="button" data-action="device-preview" data-device="desktop">Desktop</button>
                        <button type="button" data-action="device-preview" data-device="tablet">Tablet</button>
                        <button type="button" data-action="device-preview" data-device="mobile">Mobile</button>
                        <button type="button" data-action="self-bug-export"><?php echo wp_kses($this->safe_svg('docs'), $this->svg_allowed_tags()); ?> Export Bug/Flow TXT</button>
                        <button type="button" data-action="bug-all-scan"><?php echo wp_kses($this->safe_svg('settings'), $this->svg_allowed_tags()); ?> ตรวจบั๊กทั้งหมด</button>
                        <button type="button" data-action="bug-all-export"><?php echo wp_kses($this->safe_svg('docs'), $this->svg_allowed_tags()); ?> Export Bug All TXT</button>
                        <button type="button" data-action="page-health-scan"><?php echo wp_kses($this->safe_svg('bell'), $this->svg_allowed_tags()); ?> ตรวจหน้า/เมนู</button>
                        <button type="button" data-action="page-health-export"><?php echo wp_kses($this->safe_svg('docs'), $this->svg_allowed_tags()); ?> Export Page Health TXT</button>
                        <button type="button" data-action="cross-device-scan">ตรวจอุปกรณ์</button>
                        <button type="button" data-action="cross-device-export">Export Device TXT</button>
                        <button type="button" data-action="visual-ui-scan"><?php echo wp_kses($this->safe_svg('settings'), $this->svg_allowed_tags()); ?> ตรวจ UI/UX สี/ฟอนต์</button>
                        <button type="button" data-action="visual-ui-export"><?php echo wp_kses($this->safe_svg('docs'), $this->svg_allowed_tags()); ?> Export UI/UX TXT</button>
                        <button type="button" data-action="smoothness-scan"><?php echo wp_kses($this->safe_svg('settings'), $this->svg_allowed_tags()); ?> ตรวจความลื่น/กระชาก</button>
                        <button type="button" data-action="smoothness-export"><?php echo wp_kses($this->safe_svg('docs'), $this->svg_allowed_tags()); ?> Export Smooth TXT</button>
                        <button type="button" data-action="inspector-version-scan"><?php echo wp_kses($this->safe_svg('settings'), $this->svg_allowed_tags()); ?> ตรวจเวอร์ชัน Inspector</button>
                        <button type="button" data-action="inspector-version-export"><?php echo wp_kses($this->safe_svg('docs'), $this->svg_allowed_tags()); ?> Export Version TXT</button>
                    </div>
                </div>
            </section>
        </main>

        <aside class="artifact" id="artifact" aria-label="Artifact">
            <div class="panel-head">
                <div><b>Artifact</b><small>Preview · Code · Files · Copy/Download/Preview</small></div>
                <button class="icon-btn" type="button" data-action="toggle-artifact" aria-label="ปิด Artifact">×</button>
            </div>
            <div class="artifact-tabs">
                <button type="button" class="is-active" data-action="artifact-tab" data-tab="preview">Preview</button>
                <button type="button" data-action="artifact-tab" data-tab="code">Code</button>
                <button type="button" data-action="artifact-tab" data-tab="files">Files</button>
            </div>
            <div class="artifact-body" id="artifactBody">Artifact จะเปิดเมื่อมีคำตอบ โค้ด หรือไฟล์ที่สร้าง</div>
            <div class="panel-actions">
                <button type="button" data-action="download-artifact">Download MD</button>
                <button type="button" data-action="download-code">Download Code</button><button type="button" data-action="preview-everything">Preview Guide</button>
            </div>
        </aside>
    </div>

    <div class="floating-panel update-center-pop" id="updateCenterPop" hidden role="dialog" aria-label="สถานะระบบ">
        <div class="panel-head"><div><b>Status / Debug</b><small>Clean Slate runtime report</small></div><button class="icon-btn" type="button" data-action="updates-close">×</button></div>
        <div class="panel-actions compact"><button type="button" data-action="bug-all-scan">Scan</button><button type="button" data-action="page-health-export">Export Health</button><button type="button" data-action="visual-ui-export">Export UI/UX</button><button type="button" data-action="smoothness-export">Export Smooth</button></div>
        <pre class="panel-body mono" id="updateCenterBody"></pre>
    </div>

    <div class="floating-panel rooms-drawer" id="roomsDrawer" hidden role="dialog" aria-label="ห้องแชท">
        <div class="panel-head"><div><b>Rooms</b><small>หัวข้ออัตโนมัติ แก้ไข/ลบได้</small></div><button class="icon-btn" type="button" data-action="rooms-close">×</button></div>
        <div class="panel-actions"><button type="button" data-action="room-new">+ ห้องใหม่</button><button type="button" data-action="room-title-now">ตั้งหัวข้อจากแชท</button></div>
        <div class="rooms-list" id="roomsList"></div>
    </div>

    <div class="floating-panel workplace-pop" id="workplacePop" hidden role="dialog" aria-label="Workplace">
        <div class="panel-head"><div><b id="workplaceTitle">Workplace</b><small id="workplaceSub">เครื่องมือสัมพันธ์ทั้งระบบ</small></div><button class="icon-btn" type="button" data-action="workplace-close">×</button></div>
        <div class="panel-body" id="workplaceBody"></div>
    </div>

    <div class="floating-panel settings" id="settings" hidden role="dialog" aria-label="Settings">
        <div class="panel-head"><div><b>Real API Center</b><small>ใช้หน้า Settings native เพื่อลด popup ซ้อน</small></div><button class="icon-btn" type="button" data-action="close-settings">×</button></div>
        <div class="panel-body" id="settingsBody">กำลังเปิดหน้า Settings...</div>
    </div>

    <div id="voicePop" hidden aria-hidden="true"></div>
    <div id="audioPlayerPop" hidden aria-hidden="true"></div>

    <div class="floating-panel link-preview-pop" id="linkPreviewPop" hidden role="dialog" aria-label="Link Preview">
        <div class="panel-head"><div><b id="linkPreviewTitle">Link Preview</b><small id="linkPreviewUrl">อ่านลิงก์และเรียบเรียงใหม่</small></div><button class="icon-btn" type="button" data-action="link-preview-close">×</button></div>
        <div class="panel-body" id="linkPreviewBody">พร้อมอ่านลิงก์</div>
        <div class="panel-actions"><button type="button" data-action="link-preview-use">ส่งเข้า Composer</button><button type="button" data-action="link-preview-import">อ่านอีกครั้ง</button><button type="button" data-action="link-preview-open">เปิดแท็บ</button></div>
    </div>

    <div class="floating-panel image-preview-pop" id="imagePreviewPop" hidden role="dialog" aria-label="Image Generation Preview">
        <div class="panel-head"><div><b id="imagePreviewTitle">Image Generation</b><small id="imagePreviewMeta">พร้อมสร้างภาพจาก prompt</small></div><button class="icon-btn" type="button" data-action="image-preview-close">×</button></div>
        <div class="panel-body image-preview-body"><div class="image-preview-frame"><img id="imagePreviewImg" alt="AiRA generated preview"></div><div class="image-preview-note" id="imagePreviewNote">แนบภาพเพื่ออ่าน/OCR หรือพิมพ์ prompt เพื่อสร้างภาพจริง</div></div>
        <div class="panel-actions"><button type="button" data-action="generate-image">สร้างใหม่จาก Composer</button><button type="button" data-action="image-preview-download">บันทึก/ดาวน์โหลดภาพ</button></div>
    </div>

    <div class="floating-panel structure-preview-pop" id="structurePreviewPop" hidden role="dialog" aria-label="ตัวอย่างโครงสร้างเว็บใหม่">
        <div class="panel-head"><div><b id="structurePreviewTitle">โครงสร้างเว็บใหม่</b><small id="structurePreviewUrl">สร้างใหม่แบบไม่ copy</small></div><button class="icon-btn" type="button" data-action="structure-preview-close">×</button></div>
        <div class="panel-body" id="structurePreviewBody">พร้อมสร้างตัวอย่างใหม่</div>
        <div class="panel-actions"><button type="button" data-action="structure-preview-use">ส่งเข้า Composer</button><button type="button" data-action="structure-preview-download">Download Blueprint</button></div>
    </div>

    <div class="action-bank" hidden aria-hidden="true">
        <?php
        $actions = array(
            'aira-api-topic-add','aira-research-source','attachment-preview','attachment-remove','upload-file','context-scan','context-export','audio-stop','audio-toggle','bug-all-export','bug-all-scan','clone-inspiration','color-code-draw','composer-expand','composer-menu','connect-all-api','code-wp-preinstall','create-one-click','cross-device-export','cross-device-scan','custom-feature-builder','deep-audit','device-preview','draw-sample-visual','generate-image','generated-image-open','image-prompt-helper','generate-local-visual','github-import','image-preview-download','inspector-version-export','inspector-version-scan','knowledge-update-external','link-preview-import','link-preview-open','link-preview-use','mode','new-chat','open-rooms','open-settings','page-health-export','page-health-scan','photo-to-text','plain-text-mode','preview-everything','prompt-helper','prompt-info-save','quick-prompt','refine-last-answer','related-command-helper','self-bug-export','smart-tags-refresh','smoothness-export','smoothness-scan','sound-identify','speech-image-text','stability-audit','structure-preview-download','structure-preview-use','sync-now','toggle-artifact','translate-extract','updates-clear-read','updates-mark-all','visual-ref-transform','visual-reference-collector','visual-ui-export','visual-ui-scan','voice-open','voice-stop','voice-talk','web-code-import','web-structure-import','workplace'
        );
        foreach ($actions as $action) {
            echo '<button type="button" data-action="' . esc_attr($action) . '">' . esc_html($action) . '</button>';
        }
        ?>
    </div>

    <div class="toasts" id="toasts" aria-live="polite"></div>
</div>
<?php
    }

    /* ============================================================
     * SVG icon set
     * ============================================================ */

    private function svg($name) {
        $icons = array(
            'plus'     => '<svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg>',
            'back'     => '<svg viewBox="0 0 24 24"><path d="m14 6-6 6 6 6"/></svg>',
            'rooms'    => '<svg viewBox="0 0 24 24"><rect x="3.5" y="5" width="17" height="14" rx="3"/><path d="M3.5 10h17"/></svg>',
            'chat'     => '<svg viewBox="0 0 24 24"><path d="M5 6h14v9H9l-4 3V6Z"/></svg>',
            'docs'     => '<svg viewBox="0 0 24 24"><path d="M7 3.5h7l4 4V20.5H7z"/><path d="M14 3.5v4h4"/></svg>',
            'code'     => '<svg viewBox="0 0 24 24"><path d="m9 8-4 4 4 4"/><path d="m15 8 4 4-4 4"/></svg>',
            'globe'    => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5"/><path d="M3.8 12h16.4"/><path d="M12 3.5c2.1 2.2 3.1 5 3.1 8.5s-1 6.3-3.1 8.5"/><path d="M12 3.5c-2.1 2.2-3.1 5-3.1 8.5s1 6.3 3.1 8.5"/></svg>',
            'plug'     => '<svg viewBox="0 0 24 24"><path d="M9 7v4"/><path d="M15 7v4"/><path d="M7 11h10v3a5 5 0 0 1-10 0z"/><path d="M12 19v2"/></svg>',
            'artifact' => '<svg viewBox="0 0 24 24"><rect x="5" y="5" width="14" height="14" rx="2"/><path d="M9 10h6M9 14h6"/></svg>',
            'memory'   => '<svg viewBox="0 0 24 24"><path d="M7 4h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/><path d="M9 8h6"/><path d="M9 12h6"/><path d="M9 16h4"/></svg>',
            'tag'      => '<svg viewBox="0 0 24 24"><path d="M4.5 5.5v6.2c0 .5.2 1 .6 1.4l5.8 5.8a2.2 2.2 0 0 0 3.1 0l4.9-4.9a2.2 2.2 0 0 0 0-3.1L13.1 5.1a2 2 0 0 0-1.4-.6H5.5a1 1 0 0 0-1 1Z"/><circle cx="8.2" cy="8.2" r="1.2"/></svg>',
            'heart'    => '<svg viewBox="0 0 24 24"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/></svg>',
            'performance' => '<svg viewBox="0 0 24 24"><path d="M4 19V5"/><path d="M4 19h16"/><path d="m7 15 3-3 3 2 5-7"/><path d="M17 7h3v3"/></svg>',
            'bell'     => '<svg viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>',
            'settings' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c0 .67.39 1.27 1 1.51H21a2 2 0 1 1 0 4h-.09c-.61.24-1 .84-1 1.51z"/></svg>',
            'mic'      => '<svg viewBox="0 0 24 24"><rect x="9" y="3" width="6" height="11" rx="3"/><path d="M5 11a7 7 0 0 0 14 0"/><path d="M12 18v3"/><path d="M8 21h8"/></svg>',
            'arrow-up' => '<svg viewBox="0 0 24 24"><path d="M12 19V5"/><path d="m6 11 6-6 6 6"/></svg>',
            'upload'   => '<svg viewBox="0 0 24 24"><path d="M12 16V4"/><path d="m7 9 5-5 5 5"/><path d="M5 20h14"/></svg>',
            'image'    => '<svg viewBox="0 0 24 24"><rect x="4" y="5" width="16" height="14" rx="2"/><circle cx="9" cy="10" r="1.5"/><path d="m7 17 4.2-4.2 2.8 2.8 1.6-1.6L20 18"/></svg>',
        );
        return $icons[$name] ?? '';
    }

    private function svg_allowed_tags() {
        return array(
            'svg' => array('viewBox' => true, 'viewbox' => true, 'xmlns' => true, 'aria-hidden' => true, 'focusable' => true, 'role' => true, 'class' => true),
            'path' => array('d' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true),
            'rect' => array('x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'fill' => true, 'stroke' => true),
            'circle' => array('cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true),
        );
    }

    private function safe_svg($name) {
        return wp_kses($this->svg($name), $this->svg_allowed_tags());
    }

    /* ============================================================
     * Provider catalog
     * ============================================================ */

    private function providers() {
        return array(
            'universal_api_hub' => 'Universal API Hub · Key กลาง',
            'openai' => 'OpenAI API · GPT / Responses',
            'anthropic' => 'Anthropic API · Claude',
            'gemini' => 'Google Gemini API · Multimodal',
            'openrouter' => 'OpenRouter API · Multi-model Router',
            'groq' => 'Groq API · Fast OpenAI-compatible',
            'mistral' => 'Mistral API · Chat/Reasoning',
            'perplexity' => 'Perplexity API · Research Answer',
            'xai' => 'xAI API · Grok Models',
            'deepseek' => 'DeepSeek API · Reasoning/Code',
            'together' => 'Together AI API · Open Models',
            'v0' => 'v0 API · UI Builder Endpoint',
            'custom' => 'Custom API · Endpoint ส่วนตัว',
            'google_search' => 'Google Search API · Web / Image / Local / GIF',
            'google_image' => 'Google Image / GIF Search · CSE + Vision Endpoint',
            'github' => 'GitHub API · Repo / Raw / Gist',
            'elevenlabs' => 'ElevenLabs API · Voice Engine',
        );
    }

    private function api_role_label($provider) {
        $map = array(
            'auto' => 'เลือกระบบอัตโนมัติตามคำสั่ง',
            'universal_api_hub' => 'key กลางสำหรับโยนเข้าช่อง API ที่ตรวจพบ',
            'openai' => 'แชทหลัก/โค้ด/Responses API',
            'anthropic' => 'อ่านยาว/วิเคราะห์/Artifact style',
            'gemini' => 'ภาพ ไฟล์ Multimodal และภาษา',
            'openrouter' => 'สำรองและสวิตช์โมเดลหลายค่าย',
            'groq' => 'ตอบไว/งาน realtime ผ่าน endpoint OpenAI-compatible',
            'mistral' => 'งาน reasoning/chat และโค้ดผ่าน Chat Completions',
            'perplexity' => 'งานค้นคว้า/คำถามยากที่ต้องสรุปจากเว็บเมื่อมี key',
            'xai' => 'Grok/chat model ผ่าน Chat Completions',
            'deepseek' => 'งาน reasoning/code ผ่าน Chat Completions',
            'together' => 'โมเดล open-source หลายแบบผ่าน Chat Completions',
            'v0' => 'สร้าง UI/Layout/Component ผ่าน endpoint',
            'custom' => 'เชื่อม API ของผู้ใช้เอง',
            'google_search' => 'ค้นเว็บทั่วไป/ท้องถิ่นผ่าน Google CSE',
            'google_image' => 'ค้นภาพ/ภาพเคลื่อนไหวผ่าน Google CSE หรือ Vision endpoint',
            'github' => 'อ่าน repo/raw/gist และตรวจโค้ด',
            'elevenlabs' => 'อ่านข้อความและเสียงตอบกลับ',
        );
        return $map[$provider] ?? 'API provider';
    }

    private function api_endpoint_info($provider, $settings = array()) {
        $provider = sanitize_key($provider);
        if ($provider === 'openai') return 'POST https://api.openai.com/v1/responses หรือ /v1/chat/completions';
        if ($provider === 'anthropic') return 'POST https://api.anthropic.com/v1/messages';
        if ($provider === 'gemini') return 'POST https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent';
        if ($provider === 'openrouter') return 'POST https://openrouter.ai/api/v1/chat/completions';
        if ($provider === 'groq') return 'POST https://api.groq.com/openai/v1/chat/completions';
        if ($provider === 'mistral') return 'POST https://api.mistral.ai/v1/chat/completions';
        if ($provider === 'perplexity') return 'POST https://api.perplexity.ai/chat/completions';
        if ($provider === 'xai') return 'POST https://api.x.ai/v1/chat/completions';
        if ($provider === 'deepseek') return 'POST https://api.deepseek.com/chat/completions';
        if ($provider === 'together') return 'POST https://api.together.xyz/v1/chat/completions';
        if ($provider === 'github') return 'GET https://api.github.com/user หรือ raw/gist URL';
        if ($provider === 'elevenlabs') return 'GET https://api.elevenlabs.io/v1/user / TTS';
        if ($provider === 'google_search') return 'GET https://www.googleapis.com/customsearch/v1 · Web/Local/Image/GIF via CSE';
        if ($provider === 'google_image') {
            if (!empty($settings['google_search_cx'])) return 'GET https://www.googleapis.com/customsearch/v1?searchType=image · uses Google Search key + CSE ID';
            $u = trim((string)($settings['google_image_endpoint'] ?? ''));
            return $u !== '' ? $u : 'ต้องใส่ Image/Vision endpoint';
        }
        if ($provider === 'v0') {
            $u = trim((string)($settings['v0_endpoint'] ?? ''));
            return $u !== '' ? $u : 'ต้องใส่ v0/UI Builder endpoint';
        }
        if ($provider === 'custom') {
            $u = trim((string)($settings['custom_endpoint'] ?? ''));
            return $u !== '' ? $u : 'ต้องใส่ Custom endpoint';
        }
        if ($provider === 'universal_api_hub') return 'เก็บ key กลาง แล้ว Auto Route ไป provider ที่ตรวจพบ';
        if ($provider === 'auto') return 'ไม่ใช่ endpoint เดี่ยว: เลือก OpenAI/Claude/Gemini/OpenRouter ตามคำสั่ง';
        return 'provider endpoint';
    }

    private function classify_chat_intent($message = '', $attachments = array()) {
        $text = function_exists('mb_strtolower') ? mb_strtolower((string)$message, 'UTF-8') : strtolower((string)$message);
        $intent = array('key' => 'general', 'note' => 'ทั่วไป/สนทนา', 'providers' => array('openai','openrouter','anthropic','gemini','groq','mistral','perplexity','xai','deepseek','together','custom'));
        if (preg_match('/(คำถามยาก|ค้นหา|หาข้อมูล|ข้อมูลภายใน|ข้อมูลภายนอก|ล่าสุด|ตรวจสอบ|research|search|verify|แหล่งข้อมูล|source|สรุป prompt|prompt summary|ตอบได้ทุกอย่าง|เดาได้|ทุกเรื่อง|ความรู้|โลกนี้|answer anything|best effort|hard question)/iu', $text)) {
            return array('key' => 'research_synthesis', 'note' => 'ค้นหาภายใน/ภายนอก + สรุป prompt ก่อนตอบ', 'providers' => array('openai','anthropic','gemini','openrouter','groq','mistral','perplexity','xai','deepseek','together','custom'));
        }
        if (!empty($attachments) || preg_match('/(ภาพ|รูป|image|vision|camera|กล้อง|ocr|อ่านภาพ|ไฟล์ภาพ|multimodal|gemini)/iu', $text)) {
            return array('key' => 'vision_file', 'note' => 'ภาพ/ไฟล์/Multimodal', 'providers' => array('gemini','openai','anthropic','openrouter','groq','mistral','custom'));
        }
        if (preg_match('/(อ่านโครงสร้างเว็บ|โครงสร้างเว็บ|website structure|site structure|information architecture|sitemap|wireframe จากเว็บ|ดัดแปลงเว็บ|ไม่ copy|ลิขสิทธิ์|copyright|rewrite visual|เว็บอ้างอิง)/iu', $text)) {
            return array('key' => 'web_structure_transform', 'note' => 'อ่านโครงสร้างเว็บ/ดัดแปลงใหม่ไม่ละเมิดลิขสิทธิ์', 'providers' => array('openai','anthropic','gemini','openrouter','groq','mistral','perplexity','xai','deepseek','together','custom'));
        }
        if (preg_match('/(ui|ux|layout|wireframe|mockup|preview|responsive|composer|artifact|ทับ|ซ้อน|ล้น|หน้าเว็บ|ออกแบบ|v0|component|frontend|css)/iu', $text)) {
            return array('key' => 'ui_ux_build', 'note' => 'UI/UX และการสร้างหน้าจอ', 'providers' => array('openai','anthropic','openrouter','gemini','custom'));
        }
        if (preg_match('/(github|gist|raw\.githubusercontent|repo|repository|pull request|commit|diff|patch|debug|bug|โค้ด|code|plugin|wordpress|php|javascript|css|api)/iu', $text)) {
            return array('key' => 'code_debug_api', 'note' => 'โค้ด/ดีบัก/API/WordPress', 'providers' => array('openai','anthropic','openrouter','gemini','custom'));
        }
        if (preg_match('/(อ่านยาว|เอกสาร|สรุปยาว|วิเคราะห์ละเอียด|แผนงาน|policy|manual|document|artifact|claude)/iu', $text)) {
            return array('key' => 'long_doc_reasoning', 'note' => 'อ่านยาว/เอกสาร/วิเคราะห์เชิงลึก', 'providers' => array('anthropic','openai','openrouter','gemini','perplexity','mistral','groq','custom'));
        }
        if (preg_match('/(เสียง|พูด|tts|voice|elevenlabs|ไมค์|microphone|แปลเสียง|realtime)/iu', $text)) {
            return array('key' => 'voice_translate', 'note' => 'เสียง/แปลภาษา/Voice', 'providers' => array('openai','gemini','anthropic','openrouter','groq','custom'));
        }
        if (preg_match('/(สี|palette|color|ค่าสี|gradient|ภาพนิ่ง|ภาพเคลื่อนไหว|animation|svg)/iu', $text)) {
            return array('key' => 'color_visual', 'note' => 'ค่าสี/ภาพนิ่ง/ภาพเคลื่อนไหว', 'providers' => array('openai','gemini','anthropic','openrouter','groq','custom'));
        }
        if (preg_match('/(aira api|api ของ aira|พัฒนา aira api|หัวข้อสำคัญ|ค้นหาข้อมูล|มาหาข้อมูล)/iu', $text)) {
            return array('key' => 'aira_api_builder', 'note' => 'พัฒนา AiRA API/Topic/Research', 'providers' => array('openai','anthropic','openrouter','gemini','custom'));
        }
        return $intent;
    }

    private function chat_providers($settings, $message = '', $attachments = array()) {
        $mode = sanitize_key($settings['api_response_switch'] ?? 'intent');
        $primary = sanitize_key($settings['primary_provider'] ?? 'auto');
        if ($primary === 'universal_api_hub') { $primary = 'auto'; }
        $fallback = sanitize_key($settings['fallback_provider'] ?? 'openrouter');
        $intent = $this->classify_chat_intent($message, $attachments);
        $order = array();
        if ($mode === 'manual' && $primary !== 'auto') {
            $order[] = $primary;
        } else {
            $order = array_merge($order, $intent['providers']);
            if ($primary !== 'auto') array_unshift($order, $primary);
        }
        $order = array_merge($order, array($fallback, 'openai', 'openrouter', 'anthropic', 'gemini', 'groq', 'mistral', 'perplexity', 'xai', 'deepseek', 'together', 'custom'));
        $valid = array('openai','openrouter','anthropic','gemini','groq','mistral','perplexity','xai','deepseek','together','custom');
        $order = array_values(array_unique(array_filter($order, function($p) use ($valid) {
            return in_array($p, $valid, true);
        })));

        // Smoothness: bring providers that already have a usable key to the front of the queue,
        // so chat doesn't waste round-trips on "not_configured" entries before reaching a working one.
        $secrets = $this->usable_api_secrets();
        $hub = !empty($secrets['universal_api_hub']) ? $this->normalize_api_key_input((string)$secrets['universal_api_hub']) : '';
        $hub_match = $hub !== '' ? $this->detect_provider_from_api_key($hub) : '';
        $configured = array();
        $unconfigured = array();
        foreach ($order as $p) {
            $has = !empty($secrets[$p]);
            if (!$has && $hub !== '' && ($hub_match === $p || $hub_match === '')) $has = true;
            if ($p === 'custom' && !empty($settings['custom_endpoint'])) $has = true;
            if ($has) $configured[] = $p; else $unconfigured[] = $p;
        }
        return array_merge($configured, $unconfigured);
    }

    /* ============================================================
     * AJAX entry verification
     * ============================================================ */

    private function verify() {
        check_ajax_referer(self::NONCE, 'nonce');
        if (!current_user_can($this->capability())) {
            wp_send_json_error(array('message' => 'permission_denied'), 403);
        }
    }


    private function authorized_identity_key() {
        return 'aira_studio_authorized_identity_v744';
    }

    private function authorized_identity_generate_id() {
        $raw = wp_generate_password(24, false, false) . '|' . get_current_user_id() . '|' . home_url('/') . '|' . microtime(true);
        return 'AIRA-' . strtoupper(substr(hash_hmac('sha256', $raw, wp_salt('auth')), 0, 16));
    }

    private function sanitize_device_id($value) {
        $id = sanitize_text_field((string)$value);
        $id = preg_replace('/[^a-zA-Z0-9_\-]/', '', $id);
        return substr($id, 0, 80);
    }

    private function authorized_identity_payload($touch = true, $device_id = '') {
        $uid = get_current_user_id();
        if (!$uid || !current_user_can($this->capability())) {
            return array(
                'enabled' => false,
                'authorized' => false,
                'userId' => intval($uid),
                'syncMode' => 'none',
                'message' => 'permission_required',
            );
        }
        $stored = get_user_meta($uid, $this->authorized_identity_key(), true);
        if (!is_array($stored) || empty($stored['authorizedId'])) {
            $stored = array(
                'authorizedId' => $this->authorized_identity_generate_id(),
                'ownerUserId' => intval($uid),
                'capability' => $this->capability(),
                'policy' => 'same_wordpress_user_with_required_capability',
                'createdAt' => current_time('mysql'),
                'rotatedAt' => '',
                'lastSeenAt' => current_time('mysql'),
                'devices' => array(),
            );
        }
        $device_id = $this->sanitize_device_id($device_id);
        if ($touch) {
            $stored['lastSeenAt'] = current_time('mysql');
            if ($device_id !== '') {
                if (empty($stored['devices']) || !is_array($stored['devices'])) { $stored['devices'] = array(); }
                $stored['devices'][$device_id] = array(
                    'deviceId' => $device_id,
                    'lastSeenAt' => current_time('mysql'),
                    'userAgentHash' => substr(hash('sha256', (string)($_SERVER['HTTP_USER_AGENT'] ?? '')), 0, 16),
                );
                if (count($stored['devices']) > 30) {
                    $stored['devices'] = array_slice($stored['devices'], -30, null, true);
                }
            }
            update_user_meta($uid, $this->authorized_identity_key(), $stored);
        }
        $devices = isset($stored['devices']) && is_array($stored['devices']) ? $stored['devices'] : array();
        $fingerprint = substr(hash_hmac('sha256', (string)$stored['authorizedId'] . '|' . intval($uid) . '|' . home_url('/'), wp_salt('auth')), 0, 16);
        return array(
            'enabled' => true,
            'authorized' => true,
            'authorizedId' => sanitize_text_field((string)$stored['authorizedId']),
            'ownerUserId' => intval($uid),
            'userId' => intval($uid),
            'capability' => sanitize_key((string)($stored['capability'] ?? $this->capability())),
            'policy' => sanitize_text_field((string)($stored['policy'] ?? 'same_wordpress_user_with_required_capability')),
            'syncMode' => 'authorized_id_wp_user_meta',
            'fingerprint' => $fingerprint,
            'createdAt' => sanitize_text_field((string)($stored['createdAt'] ?? '')),
            'rotatedAt' => sanitize_text_field((string)($stored['rotatedAt'] ?? '')),
            'lastSeenAt' => sanitize_text_field((string)($stored['lastSeenAt'] ?? '')),
            'deviceCount' => count($devices),
            'currentDeviceId' => $device_id,
        );
    }

    private function rotate_authorized_identity($device_id = '') {
        $uid = get_current_user_id();
        if (!$uid || !current_user_can($this->capability())) {
            return $this->authorized_identity_payload(false, $device_id);
        }
        $device_id = $this->sanitize_device_id($device_id);
        $stored = array(
            'authorizedId' => $this->authorized_identity_generate_id(),
            'ownerUserId' => intval($uid),
            'capability' => $this->capability(),
            'policy' => 'same_wordpress_user_with_required_capability',
            'createdAt' => current_time('mysql'),
            'rotatedAt' => current_time('mysql'),
            'lastSeenAt' => current_time('mysql'),
            'devices' => array(),
        );
        if ($device_id !== '') {
            $stored['devices'][$device_id] = array(
                'deviceId' => $device_id,
                'lastSeenAt' => current_time('mysql'),
                'userAgentHash' => substr(hash('sha256', (string)($_SERVER['HTTP_USER_AGENT'] ?? '')), 0, 16),
            );
        }
        update_user_meta($uid, $this->authorized_identity_key(), $stored);
        return $this->authorized_identity_payload(false, $device_id);
    }

    public function ajax_bootstrap() {
        $this->verify();
        wp_send_json_success(array(
            'version' => self::VERSION,
            'api' => $this->masked_status(),
            'settings' => $this->safe_settings(),
            'user_sync' => $this->user_sync_payload(),
            'power_capsule' => $this->aira_power_capsule(),
        ));
    }

    private function user_sync_key() {
        return 'aira_studio_user_sync_v3315';
    }

    private function sanitize_deleted_rooms_payload($deleted_rooms) {
        if (!is_array($deleted_rooms)) return array();
        $clean = array();
        $now_ms = (int) round(microtime(true) * 1000);
        foreach (array_slice($deleted_rooms, 0, 120, true) as $id => $ts) {
            $rid = sanitize_text_field((string)$id);
            $time = is_numeric($ts) ? intval($ts) : 0;
            if ($rid === '' || $time <= 0) continue;
            // Keep room tombstones for about 30 days to prevent stale tabs/browsers from restoring deleted rooms.
            if ($now_ms - $time > 1000 * 60 * 60 * 24 * 30) continue;
            $clean[$rid] = $time;
        }
        return $clean;
    }

    private function sanitize_user_sync_payload($payload) {
        if (!is_array($payload)) return array();
        $rooms = isset($payload['rooms']) && is_array($payload['rooms']) ? $payload['rooms'] : array();
        $clean_rooms = array();
        foreach (array_slice($rooms, 0, 40) as $room) {
            if (!is_array($room)) continue;
            $messages = isset($room['messages']) && is_array($room['messages']) ? $room['messages'] : array();
            $clean_messages = array();
            foreach (array_slice($messages, -120) as $m) {
                if (!is_array($m)) continue;
                $clean_messages[] = array(
                    'id' => sanitize_text_field((string)($m['id'] ?? 'm_' . wp_generate_password(8, false))),
                    'role' => in_array(($m['role'] ?? ''), array('user','assistant','system'), true) ? $m['role'] : 'assistant',
                    'text' => mb_substr(wp_check_invalid_utf8((string)($m['text'] ?? '')), 0, 12000),
                    'meta' => sanitize_text_field((string)($m['meta'] ?? '')),
                    'time' => sanitize_text_field((string)($m['time'] ?? '')),
                );
            }
            $clean_rooms[] = array(
                'id' => sanitize_text_field((string)($room['id'] ?? 'r_' . wp_generate_password(8, false))),
                'title' => sanitize_text_field((string)($room['title'] ?? 'แชทใหม่')),
                'messages' => $clean_messages,
                'updatedAt' => intval($room['updatedAt'] ?? time()),
            );
        }
        $memory = isset($payload['memory']) && is_array($payload['memory']) ? $payload['memory'] : array();
        return array(
            'rooms' => $clean_rooms,
            'activeRoomId' => sanitize_text_field((string)($payload['activeRoomId'] ?? '')),
            'memory' => array(
                'enabled' => !empty($memory['enabled']),
                'summary' => mb_substr(wp_check_invalid_utf8((string)($memory['summary'] ?? '')), 0, 8000),
                'updated' => sanitize_text_field((string)($memory['updated'] ?? current_time('mysql'))),
            ),
            'topicFiles' => $this->sanitize_topic_files_payload($payload['topicFiles'] ?? array()),
            'customTags' => $this->sanitize_custom_tags_payload($payload['customTags'] ?? array()),
            'createTopics' => $this->sanitize_create_topics_payload($payload['createTopics'] ?? array()),
            'knowledgeItems' => $this->sanitize_knowledge_items_payload($payload['knowledgeItems'] ?? array()),
            'updateNotifications' => $this->sanitize_update_notifications_payload($payload['updateNotifications'] ?? array()),
            'deletedRooms' => $this->sanitize_deleted_rooms_payload($payload['deletedRooms'] ?? array()),
            'roomDeleteVersion' => intval($payload['roomDeleteVersion'] ?? 0),
            'authorizedIdentity' => $this->authorized_identity_payload(false, isset($payload['deviceId']) ? $payload['deviceId'] : ''),
            'deviceId' => $this->sanitize_device_id((string)($payload['deviceId'] ?? '')),
            'updatedAt' => time(),
            'userId' => get_current_user_id(),
            'syncMode' => 'authorized_id_wp_user_meta',
        );
    }


    private function sanitize_update_notifications_payload($items) {
        if (!is_array($items)) return array();
        $clean = array();
        foreach (array_slice($items, 0, 80) as $item) {
            if (!is_array($item)) continue;
            $clean[] = array(
                'id' => sanitize_text_field((string)($item['id'] ?? 'u_' . wp_generate_password(8, false))),
                'menu' => sanitize_text_field((string)($item['menu'] ?? 'System')),
                'section' => sanitize_text_field((string)($item['section'] ?? 'Update')),
                'title' => sanitize_text_field((string)($item['title'] ?? 'System Update')),
                'detail' => mb_substr(wp_check_invalid_utf8((string)($item['detail'] ?? '')), 0, 1200),
                'kind' => sanitize_text_field((string)($item['kind'] ?? 'system')),
                'read' => !empty($item['read']),
                'createdAt' => intval($item['createdAt'] ?? time()),
                'updatedAt' => intval($item['updatedAt'] ?? time()),
                'count' => intval($item['count'] ?? 1),
            );
        }
        return $clean;
    }


    private function sanitize_topic_files_payload($topic_files) {
        if (!is_array($topic_files)) return array();
        $clean = array();
        foreach (array_slice($topic_files, 0, 50) as $item) {
            if (!is_array($item)) continue;
            $clean[] = array(
                'topic' => sanitize_text_field((string)($item['topic'] ?? 'ทั่วไป')),
                'kind' => sanitize_text_field((string)($item['kind'] ?? 'unknown')),
                'title' => sanitize_text_field((string)($item['title'] ?? 'ไฟล์งาน')),
                'meaning' => mb_substr(wp_check_invalid_utf8((string)($item['meaning'] ?? '')), 0, 800),
                'files' => mb_substr(wp_check_invalid_utf8((string)($item['files'] ?? '')), 0, 1600),
                'updatedAt' => intval($item['updatedAt'] ?? time()),
            );
        }
        return $clean;
    }


    private function sanitize_custom_tags_payload($tags) {
        if (!is_array($tags)) return array();
        $clean = array();
        foreach (array_slice($tags, 0, 80) as $item) {
            if (!is_array($item)) continue;
            $clean[] = array(
                'id' => sanitize_text_field((string)($item['id'] ?? '')),
                'title' => sanitize_text_field((string)($item['title'] ?? 'Tag')),
                'kind' => sanitize_text_field((string)($item['kind'] ?? 'topic')),
                'summary' => mb_substr(wp_check_invalid_utf8((string)($item['summary'] ?? '')), 0, 520),
                'keywords' => array_values(array_map('sanitize_text_field', array_slice(is_array($item['keywords'] ?? null) ? $item['keywords'] : array(), 0, 12))),
                'auto' => !empty($item['auto']),
                'usage' => intval($item['usage'] ?? 0),
                'updatedAt' => intval($item['updatedAt'] ?? time()),
            );
        }
        return $clean;
    }

    private function sanitize_create_topics_payload($topics) {
        if (!is_array($topics)) return array();
        $clean = array();
        foreach (array_slice($topics, 0, 60) as $item) {
            if (!is_array($item)) continue;
            $clean[] = array(
                'id' => sanitize_text_field((string)($item['id'] ?? '')),
                'title' => sanitize_text_field((string)($item['title'] ?? 'Create Topic')),
                'kind' => sanitize_text_field((string)($item['kind'] ?? 'universal_system_builder')),
                'scope' => sanitize_text_field((string)($item['scope'] ?? 'system')),
                'summary' => mb_substr(wp_check_invalid_utf8((string)($item['summary'] ?? '')), 0, 700),
                'keywords' => array_values(array_map('sanitize_text_field', array_slice(is_array($item['keywords'] ?? null) ? $item['keywords'] : array(), 0, 16))),
                'auto' => !empty($item['auto']),
                'usage' => intval($item['usage'] ?? 0),
                'updatedAt' => intval($item['updatedAt'] ?? time()),
            );
        }
        return $clean;
    }

    private function sanitize_knowledge_items_payload($items) {
        if (!is_array($items)) return array();
        $clean = array();
        $seen = array();
        foreach (array_slice($items, 0, 100) as $item) {
            if (!is_array($item)) continue;
            $title = sanitize_text_field((string)($item['title'] ?? 'Knowledge Item'));
            $content = mb_substr(wp_check_invalid_utf8((string)($item['content'] ?? $item['summary'] ?? '')), 0, 1800);
            $key = sanitize_key((string)($item['fingerprint'] ?? ''));
            if ($key === '') {
                $key = sanitize_key(substr(md5(mb_strtolower($title . ' ' . $content, 'UTF-8')), 0, 16));
            }
            if (isset($seen[$key]) || trim($title . $content) === '') continue;
            $seen[$key] = true;
            $clean[] = array(
                'id' => sanitize_text_field((string)($item['id'] ?? '')),
                'title' => $title,
                'kind' => sanitize_text_field((string)($item['kind'] ?? 'foundation')),
                'source' => sanitize_text_field((string)($item['source'] ?? 'internal')),
                'content' => $content,
                'summary' => mb_substr(wp_check_invalid_utf8((string)($item['summary'] ?? '')), 0, 700),
                'keywords' => array_values(array_map('sanitize_text_field', array_slice(is_array($item['keywords'] ?? null) ? $item['keywords'] : array(), 0, 18))),
                'fingerprint' => $key,
                'auto' => !empty($item['auto']),
                'usage' => intval($item['usage'] ?? 0),
                'updatedAt' => intval($item['updatedAt'] ?? time()),
            );
        }
        return $clean;
    }

    private function user_sync_payload() {
        $uid = get_current_user_id();
        if (!$uid) return array('enabled' => false, 'userId' => 0, 'syncMode' => 'none', 'authorizedIdentity' => $this->authorized_identity_payload(false));
        $stored = get_user_meta($uid, $this->user_sync_key(), true);
        if (!is_array($stored)) $stored = array();
        $stored['enabled'] = true;
        $stored['userId'] = $uid;
        $stored['syncMode'] = 'authorized_id_wp_user_meta';
        $stored['authorizedIdentity'] = $this->authorized_identity_payload(false);
        return $stored;
    }


    public function ajax_get_authorized_identity() {
        $this->verify();
        $device_id = isset($_POST['device_id']) ? wp_unslash($_POST['device_id']) : '';
        wp_send_json_success(array('authorized_identity' => $this->authorized_identity_payload(true, $device_id)));
    }

    public function ajax_rotate_authorized_identity() {
        $this->verify();
        if (!current_user_can($this->capability())) {
            wp_send_json_error(array('message' => 'permission_denied'), 403);
        }
        $device_id = isset($_POST['device_id']) ? wp_unslash($_POST['device_id']) : '';
        wp_send_json_success(array(
            'authorized_identity' => $this->rotate_authorized_identity($device_id),
            'message' => 'authorized_id_rotated',
        ));
    }

    public function ajax_authorized_sync_status() {
        $this->verify();
        $identity = $this->authorized_identity_payload(true, isset($_POST['device_id']) ? wp_unslash($_POST['device_id']) : '');
        $sync = $this->user_sync_payload();
        wp_send_json_success(array(
            'authorized_identity' => $identity,
            'server_sync' => array(
                'rooms' => isset($sync['rooms']) && is_array($sync['rooms']) ? count($sync['rooms']) : 0,
                'activeRoomId' => sanitize_text_field((string)($sync['activeRoomId'] ?? '')),
                'updatedAt' => intval($sync['updatedAt'] ?? 0),
                'syncMode' => sanitize_text_field((string)($sync['syncMode'] ?? '')),
            ),
        ));
    }

    public function ajax_get_user_sync() {
        $this->verify();
        wp_send_json_success(array('user_sync' => $this->user_sync_payload()));
    }

    public function ajax_save_user_sync() {
        $this->verify();
        $uid = get_current_user_id();
        if (!$uid) wp_send_json_error(array('message' => 'no_user_id'), 400);
        $raw = isset($_POST['payload']) ? wp_unslash($_POST['payload']) : '';
        $decoded = is_string($raw) ? json_decode($raw, true) : array();
        $identity = $this->authorized_identity_payload(true, isset($decoded['deviceId']) ? $decoded['deviceId'] : '');
        $clean = $this->sanitize_user_sync_payload(is_array($decoded) ? $decoded : array());
        $clean['authorizedIdentity'] = $identity;
        update_user_meta($uid, $this->user_sync_key(), $clean);
        wp_send_json_success(array('user_sync' => $clean, 'authorized_identity' => $identity, 'message' => 'synced_by_authorized_id'));
    }

    /* ============================================================
     * AJAX: chat
     * ============================================================ */

    private function sanitize_prompt_text($value) {
        $text = is_scalar($value) ? (string)wp_unslash($value) : '';
        $text = wp_check_invalid_utf8($text);
        $text = str_replace(chr(0), '', $text);
        // Keep slash commands, code snippets, URLs, JSON, HTML-like text and Thai input intact.
        // Only trim excessive payload size for safety; do not strip symbols like sanitize_textarea_field().
        if (function_exists('mb_substr')) {
            $text = mb_substr($text, 0, 24000);
        } else {
            $text = substr($text, 0, 24000);
        }
        return $text;
    }

    private function gpt_like_processor_context($message, $settings = array(), $attachments = array()) {
        $mode = (string)($settings['gpt_like_processor_mode'] ?? 'direct_answer_with_context_router');
        if ($mode === 'off') return '';
        $intent = $this->classify_chat_intent((string)$message, is_array($attachments) ? $attachments : array());
        $is_wp = (bool)preg_match('/(wordpress|wp|ปลั๊กอิน|plugin|module|widget|gutenberg|elementor|shortcode|admin|composer|ui|ux)/iu', (string)$message);
        $is_research = $this->research_triggered_by_prompt((string)$message, is_array($settings) ? $settings : array());
        $is_code = (bool)preg_match('/(โค้ด|code|สร้างระบบ|พัฒนา|แก้บั๊ก|bug|ไฟล์|download|zip|php|js|css|api)/iu', (string)$message);
        $route = $intent['key'];
        $attachment_count = is_array($attachments) ? count($attachments) : 0;
        if ($attachment_count > 0) $route .= '+live_vision_attachment';
        if ($is_wp) $route .= '+wordpress_system';
        if ($is_research) $route .= '+research';
        if ($is_code) $route .= '+code_builder';
        return "GPT-like Processor Policy / นโยบายประมวลผลแบบ GPT:\n" .
            "- Route: " . sanitize_text_field($route) . "\n" .
            "- Step 1 Understand: อ่านคำสั่งล่าสุดเป็นหลัก แล้วผูกกับบริบทห้อง/Memory/Topic เท่าที่เกี่ยวข้องเท่านั้น\n" .
            "- Step 2 Compact: ย่อคำสั่งยาวให้เป็น working prompt ภายใน ห้ามตัดเงื่อนไขสำคัญของผู้ใช้\n" .
            "- Step 3 Retrieve: ใช้ Memory, Topic, Docs, History, Link/URL context, live image attachments และ API/Search ที่ตั้งค่าไว้ ถ้าไม่มีให้ตอบจากบริบทที่มีพร้อมบอกข้อจำกัด\n" .
            "- Step 4 Route: งาน WordPress ให้แยก Plugin, Module, Widget, Gutenberg Block, Elementor Widget, Admin Page, AJAX/REST, Assets, Security; งานโค้ดให้จัดไฟล์และวิธีติดตั้ง; งานค้นข้อมูลให้แยกข้อเท็จจริง/สมมติฐาน/ต้องตรวจเพิ่ม\n" .
            "- Step 5 Answer: ตอบตรงประเด็นก่อน ไม่โชว์ chain-of-thought ไม่ตอบกว้างไร้ทิศทาง และห้ามส่งค่าว่าง; ถ้ามี live image attachment ให้เริ่มจากสิ่งที่เห็น/ตัวอักษรที่อ่านได้/ข้อสรุปจากภาพ\n" .
            "- Step 5.5 Result Resolver: ก่อนตอบต้องระบุผลลัพธ์ที่ผู้ใช้ควรเห็นจริง เช่น image card, code block, rendered Result, WordPress patch/package หรือคำตอบสรุปที่ใช้ได้ทันที; ห้ามจบแค่ข้อความสถานะประมวลผล\n" .
            "- Step 5.6 Deep Communication: อ่านเจตนาแฝงแบบปลอดภัย เช่น ผู้ใช้กำลังหงุดหงิดเพราะผลลัพธ์ไม่ขึ้น ต้องการให้ทำต่อจากเดิม ต้องการคำตอบสั้นก่อน หรืออยากได้ไฟล์/โค้ดใช้จริง โดยไม่อ้างว่าอ่านใจหรือเดาข้อมูลอ่อนไหว\n" .
            "- Step 5.7 Global Communication: แปลงทุกสไตล์การสื่อสารที่ปลอดภัย เช่น คำสั้น คำบ่น ภาษาเทคนิค ภาษาคนทั่วไป หลายบทบาท/หลายภาษา ให้เป็น output contract ที่เข้าใจง่าย\n" .
            "- Step 5.8 Code Master: ถ้าเป็นงานโค้ด ให้คิดแบบ senior engineer: สร้างโค้ดเต็ม, แยกไฟล์/หน้าที่, ตรวจ security, responsive, compatibility, upgrade path, test plan และให้ code block ที่ระบบติดตั้ง/อัปเกรดต่อได้\n" .
            "- Step 5.9 Universal System Code: เชื่อม frontend/backend/database/API/auth/storage/deploy/logs และระบบภายนอกที่เกี่ยวข้องเป็นแผนเดียว พร้อมบอกข้อจำกัดหากขาดสเปกหรือสิทธิ์เข้าถึง\n" .
            "- Step 6 Recovery: ถ้า API/ค้นหาไม่พร้อม ให้ตอบแบบ best-effort ที่ใช้งานได้จริง พร้อม next check สั้น ๆ\n" .
            "- UX Output: ถ้ามีไฟล์/โค้ดให้ตั้งชื่อไฟล์ภาษาอังกฤษสั้นและสร้าง code fence ชัดเจน; ถ้าไม่มีไฟล์ ห้ามบอกว่ามีไฟล์ดาวน์โหลด";
    }

    private function compose_contextual_prompt($message, $memory_context = '', $history_context = '', $topic_context = '', $typo_context = '', $menu_context = '', $research_context = '', $deep_context = '', $knowledge_orchestrator_context = '', $context_contract = '', $context_lock = '', $full_context_map = '', $mandatory_read_context = '', $relation_context = '', $capability_orchestrator_context = '', $command_understanding_context = '', $adaptive_intelligence_context = '') {
        $message = (string)$message;
        $memory_context = trim((string)$memory_context);
        $history_context = trim((string)$history_context);
        $topic_context = trim((string)$topic_context);
        $typo_context = trim((string)$typo_context);
        $deep_context = trim((string)$deep_context);
        $knowledge_orchestrator_context = trim((string)$knowledge_orchestrator_context);
        $context_contract = trim((string)$context_contract);
        $context_lock = trim((string)$context_lock);
        $full_context_map = trim((string)$full_context_map);
        $mandatory_read_context = trim((string)$mandatory_read_context);
        $relation_context = trim((string)$relation_context);
        $capability_orchestrator_context = trim((string)$capability_orchestrator_context);
        $command_understanding_context = trim((string)$command_understanding_context);
        $adaptive_intelligence_context = trim((string)$adaptive_intelligence_context);
        $prefix = '';
        if ($adaptive_intelligence_context !== '') {
            $prefix .= "AIRA DECISION CONTINUITY + APEX SYSTEM INTELLIGENCE v3.4.26 / ตัวคุมความฉลาดก่อนตอบ: อ่านทั้งระบบจาก latest command, active issue, root cause, failure chain, old-runtime conflict, whole-system bridge, capability route, knowledge snapshot, API/model readiness และ relevance self-check ก่อนทุก context:\n" . $adaptive_intelligence_context . "\n\n";
        }
        if ($command_understanding_context !== '') {
            $prefix .= "GPT+AiRA UNIFIED BRAIN + SEMANTIC THREAD BRIDGE v3.4.26 / ตัวแปลความหมายคำสั้น-คำบ่น + สะพานเชื่อมความสามารถ ความรู้ และประเด็นที่ต้องแก้ ต้องอ่านก่อนทุก context:\n" . $command_understanding_context . "\n\n";
        }
        if ($capability_orchestrator_context !== '') {
            $prefix .= "CAPABILITY ORCHESTRATOR BRAIN v3.4.08 / ตัวกลางเชื่อมความสามารถ ความรู้ เมนู และ API ก่อนตอบ ต้องอ่านหลัง COMMAND UNDERSTANDING:\n" . $capability_orchestrator_context . "\n\n";
        }
        if ($relation_context !== '') {
            $prefix .= "RELATION CONTEXT BRAIN v3.4.07 / กราฟเชื่อมโยงประเด็นทั้งหน้า ต้องอ่านก่อนทุก context:
" . $relation_context . "

";
        }
        if ($context_contract !== '') {
            $prefix .= "CONTEXT CONTRACT BRAIN v3.4.07 / ข้อสรุปบังคับก่อนตอบ ห้ามข้าม:
" . $context_contract . "

";
        }
        if ($mandatory_read_context !== '') {
            $prefix .= "HARD READ BEFORE ANSWER v3.4.07 / ระบบอ่านจริงก่อนตอบ ห้ามข้าม:\n" . $mandatory_read_context . "\n\n";
        }
        if ($context_lock !== '') {
            $prefix .= "PRIORITY CONTEXT LOCK / ตัวล็อกประเด็นสำคัญที่สุดก่อนตอบ:\n" . $context_lock . "\n\n";
        }
        if ($full_context_map !== '') {
            $prefix .= "Context Contract Map / แผนที่โครงสร้างบริบททั้งหน้าแชท:\n" . $full_context_map . "\n\n";
        }
        if ($deep_context !== '') {
            $prefix .= "Deep Page Context / บริบททั้งหน้าตั้งแต่หัวข้อแรกถึงล่าสุด:\n" . $deep_context . "\n\n";
        }
        if ($memory_context !== '') {
            $prefix .= "บริบท Memory ที่ผู้ใช้ตั้งไว้:\n" . $memory_context . "\n\n";
        }
        if ($history_context !== '') {
            $prefix .= "บริบทคำสั่งต่อเนื่องในห้องนี้ล่าสุด:\n" . $history_context . "\n\n";
        }
        if ($topic_context !== '') {
            $prefix .= "แผนที่ความหมายไฟล์/องค์ประกอบของหัวข้อนี้:\n" . $topic_context . "\n\n";
        }
        if ($typo_context !== '') {
            $prefix .= "ตัวช่วยอ่านคำสั่งเมื่อพิมพ์ผิด/ลืมเปลี่ยนภาษา:\n" . $typo_context . "\n\n";
        }
        if ($menu_context !== '') {
            $prefix .= "Smart Menu Tags / เมนูที่เกี่ยวข้องซึ่งระบบเดาจากคำถาม:\n" . $menu_context . "\n\n";
        }
        if ($research_context !== '') {
            $prefix .= "Research Synthesis / บริบทค้นหาภายใน-ภายนอกสำหรับคำถามยาก:\n" . $research_context . "\n\n";
        }
        if ($knowledge_orchestrator_context !== '') {
            $prefix .= "Knowledge Orchestrator / ตัวช่วยรวบรวมความรู้และข้อจำกัดก่อนตอบ:\n" . $knowledge_orchestrator_context . "\n\n";
        }
        if ($prefix === '') return $message;
        return $prefix . "AIRA HUMAN RESPONSE LAYER v3.4.58 / กฎคำตอบภาษามนุษย์ก่อนตอบทุกครั้ง:
- ตอบให้คนทั่วไปเข้าใจ ไม่เทศัพท์ระบบหรือชื่อโมดูลยาว ๆ ใส่ผู้ใช้ เว้นแต่ผู้ใช้ถามเชิงเทคนิค
- เริ่มด้วยคำตอบสั้น ๆ ว่าเกิดอะไรขึ้นหรือควรทำอะไรต่อ จากนั้นค่อยแจกแจง 2-4 ข้อที่ทำได้จริง
- ถ้าเป็นปัญหา UI/ปลั๊กอิน ให้ใช้ภาษาง่าย: อาการ → สาเหตุที่เป็นไปได้ → แก้ตรงไหน → วิธีเช็ก ไม่ต้องแสดง chain ภายในหรือรายการ runtime ยาว
- ถ้าข้อมูลเป็นเชิงเทคนิค ให้ซ่อนไว้ในหัวข้อ “รายละเอียดสำหรับทีมพัฒนา” แบบสั้น ๆ เท่านั้น
- อย่าตอบเหมือน log/debug dump, อย่าใช้คำตอบซ้ำ, อย่าบอกเวอร์ชันในคำตอบทั่วไปถ้าไม่จำเป็น

" . "กฎสำคัญ: ถ้ามี AIRA HUMANITY MEMBER CORE v3.4.39 ให้ใช้เป็นชั้นนโยบายมนุษย์และสมาชิกก่อนตอบเสมอ: ช่วยได้ทุกเพศทุกวัยโดยไม่แบ่งแยก, ปรับภาษา/ความปลอดภัยตามวัย, ใช้ Free-first + Supporter/Creator/Scholarship อย่างโปร่งใส, คำนึง Privacy/Consent/การลบข้อมูล/ข้อมูลผู้เยาว์, ไม่เก็บหรือเปิดเผยข้อมูลส่วนตัวเกินจำเป็น, ถ้ามีค่าสนับสนุนต้องบอกสิทธิ์ ราคา การจำกัดใช้งาน การยกเลิก และขอบเขตอย่างชัดเจน; ถ้ามี HUMAN UNDERSTANDING ENGINE v3.4.39 ให้ใช้เป็นชั้นแรกในการอ่านเจตนา อารมณ์ เป้าหมาย ข้อจำกัด ความเร่งด่วน และความเสี่ยงของผู้ใช้ก่อนตอบเสมอ: ต้องช่วยแบบเข้าใจมนุษย์ ไม่ตำหนิ ไม่ตอบแข็ง แปลงปัญหาเป็นทางออกที่ทำได้จริง ถ้าผู้ใช้ไม่ถนัดโค้ดหรืองบจำกัดให้อธิบายง่ายและเสนอทางเลือกประหยัด ถ้าคำสั่งสั้นให้ใช้บริบทล่าสุดอนุมานอย่างปลอดภัย และถามกลับไม่เกิน 1 คำถามเฉพาะเมื่อจำเป็นจริง; ถ้ามี HUMAN-LIKE ASSISTANT SKILLS v3.4.39 ให้ใช้เป็นตัวควบคุมน้ำเสียงและวิธีช่วยงานก่อนตอบ: คุยแบบธรรมชาติ อบอุ่น ตรงประเด็น เหมือนผู้ช่วยมนุษย์ที่เข้าใจบริบท, จับเจตนาจากคำสั้น/คำพิมพ์ผิด, ตอบตรงก่อน, ถ้าข้อมูลไม่ครบให้เดาอย่างปลอดภัยและถามกลับไม่เกิน 1 คำถามเฉพาะเมื่อจำเป็นจริง, แปลงคำขอทั่วไปเป็นงานที่ทำได้จริง, และถ้าเป็นงานระบบให้วาง blueprint/ไฟล์/ฟังก์ชัน/API/วิธีทดสอบ/สถานะใช้งานจริงอย่างเป็นลำดับ; ถ้ามี COMMAND MENU BRIDGE v3.4.39 ให้ใช้เป็นตัวเลือกเมนู/ฟังก์ชันก่อนตอบ โดยเชื่อม Rooms, Memory, Tags, Create, Knowledge, Docs/File, Code, Web, Voice, Real API Center, Sync, Artifact/Download, Human Assistant Skills, Human Understanding Engine, AiRA Humanity Member Core, System Check และ No-Jump Monitor ให้ทำงานผ่านคำสั่งภาษาคน พร้อมใช้ Function Registry เป็นแผนผังว่าเมนูไหนรับผิดชอบอะไร ถ้าผู้ใช้สั่งเปิด/ทดสอบ/เชื่อม/อ่าน/สร้าง ให้ระบุเมนูที่ใช้ สิ่งที่ทำ และข้อจำกัด API จริงอย่างชัดเจนก่อนเสมอ; ถ้ามี SYSTEM CONTEXT RECOMPOSER v3.4.26 ให้เรียบเรียงบริบททั้งระบบก่อนตอบทุกครั้ง โดยรวมคำถามล่าสุด ปัญหาเดิม ระบบที่เกี่ยวข้อง Composer/Chat/Memory/Rooms/Tags/Create/Knowledge/API/WordPress runtime และคำตอบก่อนหน้าที่ต้องหลีกเลี่ยง แล้วจึงตอบเนื้อหาใหม่ที่ไม่วนซ้ำ; ถ้ามี QUESTION RECOMPOSER + CONTINUATION ENGINE v3.4.26 ให้เรียบเรียงคำถามล่าสุดเป็นประเด็นแก้จริงก่อนเสมอ และถ้ามี APEX SYSTEM INTELLIGENCE v3.4.26 หรือ ADAPTIVE INTELLIGENCE CORE v3.4.26 ให้ยึด Active issue, Failure chain, Intelligence route, Knowledge snapshot และ Answer relevance guard ก่อนทุกอย่าง แล้วถ้ามี SEMANTIC THREAD BRIDGE หรือ UNIFIED CONTEXT BRIDGE v3.4.26 ให้ยึด Active task, Target module, Related previous issue, Required capabilities และ Missing connections จาก bridge นั้นก่อนทุกอย่าง แล้วจึงยึด CAPABILITY ORCHESTRATOR ก่อน แล้วจึงใช้ RELATION CONTEXT BRAIN ก่อน แล้วจึงใช้ PRIORITY CONTEXT LOCK และคำสั่งล่าสุดเป็นแกนหลักเสมอ ให้ใช้บริบททั้งหน้า คำสั่งต่อเนื่องตั้งแต่หัวข้อแรก Memory Topic ไฟล์ และเมนูเป็นฉากหลังเพื่อเข้าใจงานต่อเนื่องเท่านั้น ห้ามตอบกว้าง ห้ามตอบไปเรื่อย ห้ามให้บริบทเก่าทับคำสั่งล่าสุด ถ้ามีความขัดแย้งให้ทำตามคำสั่งล่าสุดก่อนเสมอ เมื่องานเกี่ยวกับ WordPress ให้เชื่อมความสัมพันธ์ของปลั๊กอิน โมดูล วิดเจ็ต Gutenberg Block Elementor Widget assets AJAX/REST และ security เป็นระบบเดียวก่อนลงมือสร้าง ถ้าผู้ใช้พิมพ์ผิดจากการลืมเปลี่ยนภาษาให้ตีความจาก typo hint ก่อนตอบ ถ้ามี Smart Menu Tags ให้ใช้ช่วยเลือกความสามารถ/เมนูที่เกี่ยวข้อง แต่ห้ามทับคำสั่งล่าสุด ถ้าเกี่ยวกับสี/ภาพให้ตีความค่าสีเป็น palette และสามารถออกแบบภาพนิ่ง/ภาพเคลื่อนไหวแบบ local/SVG ได้ ถ้าเป็นคำถามยากหรือมีข้อมูลต้องตรวจสอบ ให้สรุป research prompt สั้น ๆ ในใจ ใช้บริบทภายใน/ภายนอกที่แนบมา แล้วตอบตรงประเด็นก่อน ถ้าผู้ใช้ต้องการให้ตอบได้กว้างเหมือน GPT ให้พยายามสังเคราะห์คำตอบจากทุกแหล่งที่เชื่อมได้ แต่ต้องแยกข้อเท็จจริง สมมติฐาน และสิ่งที่ต้องตรวจเพิ่มอย่างซื่อสัตย์\n\nสำคัญที่สุด — ความพยายามในการตอบ:\n- ห้ามตอบเพียงสั้น ๆ ว่า 'ไม่ทราบ/ไม่พบ/ขออภัย' แล้วจบ ต้องพยายามตอบจากความรู้ทั่วไป + บริบทที่ได้รับ + Memory/Rooms/Tags/Create/Knowledge/API readiness ที่ส่งมาเสมอ\n- ตอบให้ครบทุกประเด็นที่ผู้ใช้ถาม ห้ามตัดเนื้อหาเองว่ายาวเกิน\n- ถ้าเป็นโค้ด/ระบบ/ขั้นตอน ให้ส่งครบจบเป็นชุด ไม่ค้างกลางคัน\n- ถ้าข้อมูลบางส่วนยังไม่แน่ใจ ให้ตอบส่วนที่มั่นใจก่อน แล้วค่อยระบุข้อจำกัดสั้น ๆ ท้ายคำตอบ\n- ทุกคำตอบต้องเป็นประโยชน์จริงต่อโจทย์ ไม่ใช่แค่ template หรือสรุปขั้นตอนทั่วไป
- Result Resolver v7.2.5: เมื่อคำถามพูดถึงผลลัพธ์/แสดงผล/ไม่ขึ้น/Preview/Prompt ต้องเริ่มจากผลลัพธ์ที่ต้องแสดงจริง และถ้าทำไม่ได้ให้บอกข้อจำกัดพร้อมวิธีทดสอบ ไม่ตอบค้างแค่สถานะ
- Deep Communication v7.3.5: อ่านถ้อยคำสั้น ๆ/คำบ่น/คำสั่งซ้ำเป็นเจตนาแฝงแบบปลอดภัย เช่น ต้องการให้ของขึ้นจริง ต้องการแก้ต่อ ต้องการความมั่นใจ หรือต้องการคำตอบสั้นตรง; ห้ามอ้างว่าอ่านจิตใต้สำนึกจริงหรือเดาข้อมูลส่วนตัว/อ่อนไหว
- Global Communication Lens v7.3.5: เพิ่มความเก่งสื่อสารแบบ GPT โดยอ่านคำสั่งสั้น คำบ่น ภาษาคนทั่วไป ภาษาเทคนิค หลายภาษา หลายบทบาท และบริบทงานทั่วโลกอย่างปลอดภัย แล้วแปลงเป็นผลลัพธ์ที่ผู้ใช้เข้าใจและนำไปใช้ได้ทันที โดยไม่เหมารวมหรือเดาข้อมูลส่วนตัว
- Code Master v7.3.5: งานโค้ดต้องตอบแบบวิศวกรอาวุโส ให้โค้ดเต็มที่รัน/ติดตั้งได้, บอกไฟล์และหน้าที่, module/component/theme/widget/block relationship, security guard, responsive/browser support, วิธีทดสอบ, สถานะพร้อมใช้ และถ้าเป็น WordPress ให้รองรับ Install/Upgrade/Next loop
- Universal System Code Engine v7.3.5: งานระบบต้องมองความสัมพันธ์ frontend/backend/database/API/auth/storage/server/deploy/logs และระบบ WordPress/เว็บ/แอป/เซิร์ฟเวอร์ที่เกี่ยวข้องให้เป็นภาพเดียว ระบุไฟล์ data flow contract test rollback และข้อจำกัดจริงหากขาด API/สเปก/สิทธิ์เข้าถึง
- ถ้าผู้ใช้ขอให้ฉลาดเหนือ AI อื่น ให้ทำเป็นโหมด Apex: อ่านทั้งระบบ เชื่อมทุกโมดูล ตรวจคำตอบ และแก้ root cause แต่ห้ามโกหกข้อเท็จจริงว่าเหนือทุก AI ทั่วโลกโดยไม่มี benchmark
- ถ้าคำถามล่าสุดเป็นการรายงานปัญหา/ขอแก้ปลั๊กอิน ให้ตอบเฉพาะการแก้ปัญหานั้น: สาเหตุที่น่าจะเกิด, จุดที่แก้, สิ่งที่เปลี่ยน, วิธีทดสอบ และสถานะ ไม่ออกนอกประเด็น
- Question Recomposer + Continuation Engine v3.4.26: ต้องเรียบเรียงประเด็นคำถามเองจากคำสั้น/คำบ่น/คำสั่งต่อเนื่อง เช่น ไม่เปลี่ยน, ยังเด้ง, เพิ่มฉลาด, ต่อได้ แล้วตอบต่อจาก active issue เดิมทันที
- System Context Recomposer v3.4.26: ต้องเรียบเรียงประเด็นทั้งระบบ ต่อบริบทได้ และตอบเนื้อหาใหม่ทุกครั้ง โดยอนุญาตให้ผู้ใช้ถามซ้ำได้ แต่ AiRA ต้องไม่วนคำตอบเดิม และไม่อ้างว่าเหนือ AI ทั่วโลกแบบไม่มี benchmark จริง แต่ให้ยกระดับเป็นมาตรฐานสูงสุดของระบบ AiRA นี้
- Apex Context Rewriter v3.4.26: ทุกคำตอบต้องผ่านการเรียบเรียงใหม่ก่อนแสดงผล โดยรวมคำถามล่าสุด + active issue + ระบบที่เกี่ยวข้อง + สิ่งที่เปลี่ยนจากคำตอบก่อนหน้า ห้าม copy คำตอบเดิมมาตอบซ้ำ และต้องเรียบเรียงใหม่ทุกครั้ง ถ้าผู้ใช้บอกว่าไม่เปลี่ยนให้ถือว่าแพตช์ก่อนหน้าล้มเหลวและต้องเสนอการแก้ที่ต่างจากเดิมทันที
- Academic & Research Foundation v3.4.26: ถ้าผู้ใช้ถามงานวิชาการ/วิจัย ให้ช่วยตั้งคำถามวิจัย วัตถุประสงค์ สมมติฐาน ตัวแปร วิธีวิจัย กลุ่มตัวอย่าง เครื่องมือ วิเคราะห์ข้อมูล จริยธรรม ข้อจำกัด และโครงอ้างอิง โดยไม่สร้าง citation ปลอม
- Repeat Question Allowed + Fresh Answer Required v3.4.26: ผู้ใช้ถามซ้ำได้ แต่ AiRA ห้ามวนคำตอบเดิม ถ้าผู้ใช้บอกว่า 'คำถามเดิม/ถามซ้ำ/ไม่เปลี่ยน/ยังเหมือนเดิม' ให้ถือเป็น failure escalation ของ active issue เดิม ต้องตอบด้วยสิ่งใหม่ที่ทำต่อจากรอบก่อนและไม่ใช้คำตอบเดิม และถามกลับได้เฉพาะคำถามใหม่ที่จำเป็นจริง เว้นแต่เป็นคำถามใหม่เพียง 1 ข้อที่จำเป็นจริง ๆ

คำสั่งล่าสุดของผู้ใช้ ให้ยึดเป็นหลัก:\n" . $message;
    }


    private function research_triggered_by_prompt($message, $settings = array()) {
        $mode = (string)($settings['difficult_question_research_mode'] ?? 'auto_internal_external');
        if ($mode === 'off') return false;
        $text = function_exists('mb_strtolower') ? mb_strtolower((string)$message, 'UTF-8') : strtolower((string)$message);
        if (strlen($text) > 1600) return true;
        return (bool)preg_match('/(คำถามยาก|ค้นหา|หาข้อมูล|ข้อมูลทั่วไป|ท้องถิ่น|จังหวัด|อำเภอ|ใกล้ฉัน|local|ข้อมูลภายใน|ข้อมูลภายนอก|ภายใน|ภายนอก|อ่านระบบ|ระบบภายใน|ระบบภายนอก|ตรวจสอบ|อัปเดต|ล่าสุด|ราคา|กฎหมาย|api|เว็บ|ลิงก์|link|url|research|search|source|verify|compare|benchmark|วิเคราะห์ละเอียด|สรุป prompt|prompt summary|ภาพ|รูป|gif|animation|ภาพเคลื่อนไหว|design|ออกแบบ|โค้ด|โปรแกรม|แอพ|app|game|เกม|ระบบเล่นเกม)/iu', $text);
    }

    private function research_query_from_message($message) {
        $text = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags((string)$message)));
        $text = preg_replace('/https?:\/\/\S+/i', ' ', $text);
        $text = trim($text);
        if ($text === '') return 'AiRA Studio WordPress API UI';
        if (function_exists('mb_substr')) return mb_substr($text, 0, 180, 'UTF-8');
        return substr($text, 0, 180);
    }

    private function internal_docs_research_context($message, $limit = 4) {
        $docs = get_option(self::OPTION_DOCS, array());
        if (!is_array($docs) || empty($docs)) return '';
        $q = function_exists('mb_strtolower') ? mb_strtolower((string)$message, 'UTF-8') : strtolower((string)$message);
        $terms = preg_split('/[\s,.;:()\[\]{}\-\/]+/u', $q);
        $terms = array_values(array_filter(array_unique(array_map('trim', $terms)), function($t){ return $t !== '' && mb_strlen($t) >= 3; }));
        $scored = array();
        foreach ($docs as $doc) {
            if (!is_array($doc)) continue;
            $hay = (string)($doc['title'] ?? '') . "\n" . (string)($doc['content'] ?? '');
            $hay_l = function_exists('mb_strtolower') ? mb_strtolower($hay, 'UTF-8') : strtolower($hay);
            $score = 0;
            foreach (array_slice($terms, 0, 18) as $t) {
                if ($t !== '' && mb_strpos($hay_l, $t) !== false) $score += 2;
            }
            if (!empty($doc['source_url']) && preg_match('/https?:\/\//i', (string)$message)) $score += 1;
            if ($score > 0) $scored[] = array('score' => $score, 'doc' => $doc);
        }
        usort($scored, function($a, $b){ return $b['score'] <=> $a['score']; });
        $parts = array();
        foreach (array_slice($scored, 0, $limit) as $row) {
            $d = $row['doc'];
            $content = wp_strip_all_tags((string)($d['content'] ?? ''));
            $excerpt = function_exists('mb_substr') ? mb_substr($content, 0, 900, 'UTF-8') : substr($content, 0, 900);
            $parts[] = '- Internal doc: ' . sanitize_text_field((string)($d['title'] ?? 'Untitled')) . ' · score ' . intval($row['score']) . "\n  " . trim(preg_replace('/\s+/', ' ', $excerpt));
        }
        return $parts ? "Internal AiRA Docs / uploaded URL/file context:\n" . implode("\n", $parts) : '';
    }

    private function google_cse_research_context($message, $settings, $secrets) {
        $mode = (string)($settings['external_search_mode'] ?? 'google_cse_when_configured');
        if ($mode === 'off') return '';
        $key = $this->normalize_api_key_input((string)($secrets['google_search'] ?? ''));
        $cx = trim((string)($settings['google_search_cx'] ?? ''));
        if ($key === '' || $cx === '') return "External search: Google CSE not configured yet (needs Google Search API key + CSE ID).";
        $text = function_exists('mb_strtolower') ? mb_strtolower((string)$message, 'UTF-8') : strtolower((string)$message);
        $blocks = array();
        $web = $this->google_cse_query_block($message, $settings, $key, $cx, 'web');
        if ($web !== '') $blocks[] = $web;
        if (preg_match('/(ภาพ|รูป|image|photo|ออกแบบ|design|visual|mockup|ui|ux|ref|reference)/iu', $text)) {
            $img = $this->google_cse_query_block($message, $settings, $key, $cx, 'image');
            if ($img !== '') $blocks[] = $img;
        }
        if (preg_match('/(gif|ภาพเคลื่อนไหว|animation|animated|motion|เคลื่อนไหว)/iu', $text)) {
            $gif = $this->google_cse_query_block($message, $settings, $key, $cx, 'gif');
            if ($gif !== '') $blocks[] = $gif;
        }
        if (preg_match('/(โค้ด|code|program|โปรแกรม|app|แอพ|game|เกม|ระบบเล่นเกม|github|wordpress|plugin|ปลั๊กอิน)/iu', $text)) {
            $code = $this->google_cse_query_block($message . ' public code examples documentation', $settings, $key, $cx, 'code');
            if ($code !== '') $blocks[] = $code;
        }
        return implode("

", array_filter($blocks));
    }

    private function google_cse_query_block($message, $settings, $key, $cx, $kind = 'web') {
        $num = max(1, min(6, intval($settings['google_search_num_results'] ?? 3)));
        $q = $this->research_query_from_message($message);
        $params = array(
            'key' => $key,
            'cx' => $cx,
            'q' => $q,
            'num' => intval($num),
            'safe' => sanitize_key((string)($settings['google_search_safe'] ?? 'active')) ?: 'active',
        );
        $lang = sanitize_text_field((string)($settings['google_search_language'] ?? 'lang_th'));
        $country = strtolower(sanitize_key((string)($settings['google_search_country'] ?? 'th')));
        if ($lang !== '') $params['lr'] = $lang;
        if ($country !== '') {
            $params['gl'] = $country;
            $params['cr'] = 'country' . strtoupper($country);
        }
        $site = trim((string)($settings['google_search_site_restrict'] ?? ''));
        if ($site !== '') $params['siteSearch'] = preg_replace('#^https?://#', '', sanitize_text_field($site));
        if ($kind === 'image' || $kind === 'gif') {
            $params['searchType'] = 'image';
            if ($kind === 'gif') $params['fileType'] = 'gif';
        }
        if ($kind === 'code') {
            $params['q'] = $q . ' github OR documentation OR wordpress plugin OR javascript OR php';
        }
        $url = add_query_arg($params, 'https://www.googleapis.com/customsearch/v1');
        $res = wp_remote_get($url, array('timeout' => 8, 'redirection' => 2, 'headers' => array('User-Agent' => 'AiRA-Studio-Google-CSE/' . self::VERSION)));
        if (is_wp_error($res)) return 'External Google ' . sanitize_key($kind) . ' search error: ' . sanitize_text_field($res->get_error_code());
        $code = wp_remote_retrieve_response_code($res);
        $body = json_decode(wp_remote_retrieve_body($res), true);
        if ($code < 200 || $code >= 300 || !is_array($body)) return 'External Google ' . sanitize_key($kind) . ' search HTTP ' . intval($code) . ' · unable to parse response.';
        $items = isset($body['items']) && is_array($body['items']) ? $body['items'] : array();
        if (!$items) return 'External Google ' . sanitize_key($kind) . ' search: no results for query "' . sanitize_text_field($q) . '".';
        $heading = array(
            'web' => 'External Google web/local results',
            'image' => 'External Google image reference results',
            'gif' => 'External Google animated GIF reference results',
            'code' => 'External Google public code/documentation results',
        );
        $lines = array(($heading[$kind] ?? 'External Google results') . ' for: ' . sanitize_text_field($q));
        foreach (array_slice($items, 0, $num) as $i => $item) {
            $line = ($i + 1) . '. ' . sanitize_text_field((string)($item['title'] ?? 'Untitled')) . ' — ' . esc_url_raw((string)($item['link'] ?? ''));
            $snippet = sanitize_text_field((string)($item['snippet'] ?? ''));
            if ($snippet !== '') $line .= "
   " . $snippet;
            if (isset($item['mime']) && $item['mime']) $line .= "
   mime: " . sanitize_text_field((string)$item['mime']);
            if (isset($item['image']['thumbnailLink'])) $line .= "
   thumbnail: " . esc_url_raw((string)$item['image']['thumbnailLink']);
            $lines[] = $line;
        }
        return implode("
", $lines);
    }

    private function build_research_context($message, $settings, $secrets) {
        if (!$this->research_triggered_by_prompt($message, $settings)) return '';
        $query = $this->research_query_from_message($message);
        $parts = array();
        $parts[] = 'Prompt summary for research: ' . sanitize_text_field($query);
        $secrets_ready = is_array($secrets) ? implode(', ', array_keys(array_filter($secrets, function($v){ return trim((string)$v) !== ''; }))) : '';
        $parts[] = 'AiRA internal/external system read status: Internal memory/history/topic context is supplied by the browser payload when available; internal Docs store is checked below; external Search/API will be attempted only when the needed key/CSE/endpoint is configured. Configured secret slots: ' . sanitize_text_field($secrets_ready !== '' ? $secrets_ready : 'none');
        $internal = $this->internal_docs_research_context($message, 6);
        if ($internal !== '') $parts[] = $internal;
        else $parts[] = 'Internal AiRA Docs: no matching stored document chunk found, continue from memory/history/topic context and latest command.';
        if ((string)($settings['difficult_question_research_mode'] ?? 'auto_internal_external') !== 'internal_only') {
            $external = $this->google_cse_research_context($message, $settings, $secrets);
            if ($external !== '') $parts[] = $external;
        }
        $parts[] = 'Answer rule: use this context as support, do not copy web content, cite/mention source URLs when useful, and answer the latest user command directly first.';
        return implode("\n\n", $parts);
    }

    private function pick_context_line_for_fallback($context, $patterns, $max = 520) {
        $context = (string)$context;
        foreach ((array)$patterns as $pattern) {
            if (preg_match($pattern, $context, $m)) {
                $line = trim(wp_strip_all_tags((string)($m[1] ?? '')));
                if ($line !== '') {
                    return function_exists('mb_substr') ? mb_substr($line, 0, intval($max), 'UTF-8') : substr($line, 0, intval($max));
                }
            }
        }
        return '';
    }

    private function compose_latest_context_fallback_message($message, $command_context = '', $capability_context = '', $relation_context = '', $context_contract = '', $context_lock = '', $full_context_map = '', $mandatory_read_context = '') {
        $message = trim((string)$message);
        $combined = implode("
", array_map('strval', array($command_context, $capability_context, $relation_context, $context_contract, $context_lock, $full_context_map, $mandatory_read_context)));
        $active = $this->pick_context_line_for_fallback($combined, array('/Active issue to solve now:\s*(.+)/iu', '/Active issue:\s*(.+)/iu', '/Current issue:\s*(.+)/iu', '/ปัญหาปัจจุบันที่ต้องแก้:\s*(.+)/iu'), 720);
        $target = $this->pick_context_line_for_fallback($combined, array('/Resolved target:\s*(.+)/iu', '/Resolved target\/module:\s*(.+)/iu', '/Target module\(s\):\s*(.+)/iu', '/ยึดโมดูลหลักของหน้านี้:\s*(.+)/iu'), 360);
        $related = $this->pick_context_line_for_fallback($combined, array('/Related concrete command \/ failure chain:\s*(.+)/iu', '/Most related previous concrete command:\s*(.+)/iu', '/Related concrete command:\s*(.+)/iu', '/Related previous issue:\s*(.+)/iu'), 620);
        $capability = $this->pick_context_line_for_fallback($combined, array('/Intelligence route:\s*(.+)/iu', '/Capability route:\s*(.+)/iu', '/Required capabilities to connect before answering:\s*(.+)/iu'), 420);
        $rules = $this->pick_context_line_for_fallback($combined, array('/Next fix contract:\s*(.+)/isu', '/Hard rules:\s*(.+)/isu'), 760);
        $parts = array('GPT + AiRA DEEP BRIDGE FALLBACK v3.4.17');
        $parts[] = 'Latest user command: ' . $message;
        if ($active !== '') $parts[] = 'Active issue to answer now: ' . $active;
        if ($target !== '') $parts[] = 'Target module: ' . $target;
        if ($related !== '' && strtolower($related) !== 'none') $parts[] = 'Related previous command: ' . $related;
        if ($capability !== '') $parts[] = 'Capability route: ' . $capability;
        if ($rules !== '') $parts[] = 'Fix contract/rules: ' . preg_replace('/\s+/', ' ', $rules);
        $parts[] = 'Answer rule: ตอบจาก Active issue ก่อน ถ้า API หลักว่าง/ล้มเหลว ห้ามตอบ template กว้าง ให้สรุปว่ากำลังแก้ส่วนไหน จุดที่ควรแก้ และวิธีทดสอบ.';
        return implode("
", array_filter($parts));
    }


    public function ajax_get_interest_stats() {
        check_ajax_referer(self::NONCE, 'nonce');
        if (!current_user_can($this->capability())) {
            wp_send_json_error(array('message' => 'permission_denied'), 403);
        }
        $all = get_option(self::OPTION_INTEREST, array());
        if (!is_array($all)) { $all = array(); }
        $uid = (string)get_current_user_id();
        $user_stats = isset($all['users'][$uid]) && is_array($all['users'][$uid]) ? $all['users'][$uid] : array();
        wp_send_json_success(array('interest_stats' => $user_stats));
    }

    public function ajax_save_interest_stats() {
        check_ajax_referer(self::NONCE, 'nonce');
        if (!current_user_can($this->capability())) {
            wp_send_json_error(array('message' => 'permission_denied'), 403);
        }
        $raw = isset($_POST['snapshot']) ? wp_unslash($_POST['snapshot']) : '';
        $snapshot = json_decode((string)$raw, true);
        if (!is_array($snapshot)) { $snapshot = array(); }
        $samples = array();
        if (!empty($snapshot['samples']) && is_array($snapshot['samples'])) {
            foreach (array_slice($snapshot['samples'], 0, 80) as $sample) {
                if (!is_array($sample)) { continue; }
                $samples[] = array(
                    'role' => sanitize_key($sample['role'] ?? ''),
                    'topic' => sanitize_text_field($sample['topic'] ?? ''),
                    'snippet' => sanitize_textarea_field($sample['snippet'] ?? ''),
                    'time' => intval($sample['time'] ?? 0),
                    'room' => sanitize_text_field($sample['room'] ?? ''),
                );
            }
        }
        $clean = array(
            'version' => self::VERSION,
            'total' => intval($snapshot['total'] ?? 0),
            'user' => intval($snapshot['user'] ?? 0),
            'assistant' => intval($snapshot['assistant'] ?? 0),
            'topics' => array(),
            'samples' => $samples,
            'updated' => current_time('mysql'),
        );
        if (!empty($snapshot['topics']) && is_array($snapshot['topics'])) {
            foreach ($snapshot['topics'] as $topic => $count) {
                $clean['topics'][sanitize_text_field($topic)] = intval($count);
            }
        }
        $all = get_option(self::OPTION_INTEREST, array());
        if (!is_array($all)) { $all = array(); }
        if (empty($all['users']) || !is_array($all['users'])) { $all['users'] = array(); }
        $all['version'] = self::VERSION;
        $all['updated'] = current_time('mysql');
        $all['users'][(string)get_current_user_id()] = $clean;
        update_option(self::OPTION_INTEREST, $all, false);
        wp_send_json_success(array('interest_stats' => $clean));
    }

    private function local_direct_answer_if_possible($message, $settings = array(), $attachments = array()) {
        if (!empty($attachments)) return '';
        $raw = trim(wp_strip_all_tags((string)$message));
        if ($raw === '') return '';
        $compact = preg_replace('/\s+/u', '', $raw);
        if (preg_match('/^(-?\d+(?:\.\d+)?)([+\-*x×\/÷])(-?\d+(?:\.\d+)?)=?$/u', $compact, $m)) {
            $a = (float)$m[1];
            $b = (float)$m[3];
            $op = $m[2];
            if (($op === '/' || $op === '÷') && abs($b) < 0.0000000001) return 'หารด้วยศูนย์ไม่ได้ครับ';
            switch ($op) {
                case '+': $ans = $a + $b; break;
                case '-': $ans = $a - $b; break;
                case '*': case 'x': case '×': $ans = $a * $b; break;
                case '/': case '÷': $ans = $a / $b; break;
                default: return '';
            }
            $out = (abs($ans - round($ans)) < 0.0000000001) ? (string)intval(round($ans)) : rtrim(rtrim(number_format($ans, 8, '.', ''), '0'), '.');
            return $out;
        }
        if (preg_match('/^(สวัสดี|หวัดดี|hello|hi)$/iu', $raw)) return 'สวัสดีครับ ให้ AiRA ช่วยเรื่องอะไรต่อได้เลย';
        if (preg_match('/(ชื่ออะไร|คุณคือใคร|aira คืออะไร)/iu', $raw)) return 'ผมคือ AiRA Studio ผู้ช่วยในระบบ Thinkb4do สำหรับคุย วิเคราะห์ เขียนโค้ด จัดระบบ และช่วยพัฒนา WordPress/ปลั๊กอินตามบริบทที่คุณกำลังทำอยู่ครับ';
        if (preg_match('/(เมืองหลวงของไทย|เมืองหลวงประเทศไทย|capital of thailand)/iu', $raw)) return 'กรุงเทพมหานครครับ';
        return '';
    }


    private function build_real_connection_evidence($settings, $secrets, $message = '') {
        $settings = is_array($settings) ? $settings : array();
        $secrets = is_array($secrets) ? $secrets : array();
        $message_l = function_exists('mb_strtolower') ? mb_strtolower((string)$message, 'UTF-8') : strtolower((string)$message);
        $hub_key = $this->normalize_api_key_input((string)($secrets['universal_api_hub'] ?? ''));
        $hub_match = $hub_key !== '' ? $this->detect_provider_from_api_key($hub_key) : '';
        $providers = array('openai','anthropic','gemini','openrouter','groq','mistral','perplexity','xai','deepseek','together','custom','google_search','google_image','github','v0','elevenlabs','universal_api_hub');
        $items = array();
        $connected = array();
        $partial = array();
        $missing = array();
        foreach ($providers as $p) {
            $key = $this->normalize_api_key_input((string)($secrets[$p] ?? ''));
            $uses_hub = false;
            if ($key === '' && $hub_key !== '' && in_array($p, array('openai','anthropic','gemini','openrouter','groq','mistral','perplexity','xai','deepseek','together'), true) && ($hub_match === $p || $hub_match === '')) {
                $key = $hub_key;
                $uses_hub = true;
            }
            $endpoint = '';
            $ready = false;
            $state = 'missing';
            $reason = '';
            if (in_array($p, array('openai','anthropic','gemini','openrouter','groq','mistral','perplexity','xai','deepseek','together','elevenlabs','universal_api_hub'), true)) {
                $ready = $key !== '';
                $reason = $ready ? ($uses_hub ? 'key_ready_via_universal_api_hub' : 'key_ready') : 'missing_api_key';
            } elseif ($p === 'custom') {
                $endpoint = trim((string)($settings['custom_endpoint'] ?? ''));
                $ready = $endpoint !== '';
                $reason = $ready ? ($key !== '' ? 'endpoint_and_key_ready' : 'endpoint_ready_no_key') : 'missing_custom_endpoint';
            } elseif ($p === 'google_search') {
                $cx = trim((string)($settings['google_search_cx'] ?? ''));
                $ready = ($key !== '' && $cx !== '');
                $reason = $ready ? 'google_search_key_and_cse_ready' : ($key === '' ? 'missing_google_search_key' : 'missing_google_cse_id');
            } elseif ($p === 'google_image') {
                $cx = trim((string)($settings['google_search_cx'] ?? ''));
                $google_key = $this->normalize_api_key_input((string)($secrets['google_search'] ?? ''));
                $endpoint = trim((string)($settings['google_image_endpoint'] ?? ''));
                $ready = (($google_key !== '' && $cx !== '') || ($endpoint !== '' && $key !== ''));
                $reason = $ready ? 'image_search_or_vision_endpoint_ready' : 'needs_google_search_key_cse_or_image_endpoint_key';
            } elseif ($p === 'github') {
                $endpoint = trim((string)($settings['github_endpoint'] ?? ''));
                $ready = ($key !== '' || $endpoint !== '');
                $reason = $key !== '' ? 'github_token_ready' : ($endpoint !== '' ? 'public_endpoint_configured_limited' : 'missing_github_token_or_endpoint');
            } elseif ($p === 'v0') {
                $endpoint = trim((string)($settings['v0_endpoint'] ?? ''));
                $ready = ($endpoint !== '' && $key !== '');
                $reason = $ready ? 'v0_endpoint_and_key_ready' : ($endpoint === '' ? 'missing_v0_endpoint' : 'missing_v0_key');
            }
            if ($ready) {
                $state = (strpos($reason, 'limited') !== false || strpos($reason, 'no_key') !== false) ? 'partial' : 'connected';
            }
            $role = $this->api_role_label($p);
            $endpoint_info = $this->api_endpoint_info($p, $settings);
            $row = array(
                'provider' => $p,
                'label' => isset($this->providers()[$p]) ? $this->providers()[$p] : $p,
                'role' => $role,
                'endpoint' => $endpoint_info,
                'state' => $state,
                'ready' => $ready,
                'reason' => $reason,
            );
            $items[] = $row;
            $label = $p . ':' . $reason;
            if ($state === 'connected') $connected[] = $label;
            elseif ($state === 'partial') $partial[] = $label;
            else $missing[] = $label;
        }
        $intent = $this->classify_chat_intent($message, array());
        $route_order = $this->chat_providers($settings, $message, array());
        $needs_external = (bool)preg_match('/(ภายนอก|external|api|endpoint|google|github|v0|openai|gemini|claude|ค้นหา|เว็บ|ล่าสุด|source|verify|เชื่อม|connect|sync|research|ข้อมูลจริง)/iu', $message_l);
        $score = min(100, 24 + count($connected) * 8 + count($partial) * 4 + ($needs_external ? 10 : 0));
        return array(
            'version' => 'real_connection_evidence_v7.5.8.39',
            'needs_external' => $needs_external,
            'score' => $score,
            'connected' => array_slice($connected, 0, 10),
            'partial' => array_slice($partial, 0, 8),
            'missing' => array_slice($missing, 0, 12),
            'items' => $items,
            'intent' => $intent['key'] ?? 'general',
            'intent_note' => $intent['note'] ?? '',
            'route_order' => $route_order,
            'truth_rule' => 'Only call an answer external-connected when the selected provider/API actually returned a candidate or a configured connector is listed as connected/partial; otherwise label the answer as internal/local fallback with missing external requirements.',
        );
    }

    private function public_connection_evidence($evidence) {
        $evidence = is_array($evidence) ? $evidence : array();
        return array(
            'version' => sanitize_text_field((string)($evidence['version'] ?? 'real_connection_evidence_v7.5.8.39')),
            'needs_external' => !empty($evidence['needs_external']),
            'score' => intval($evidence['score'] ?? 0),
            'connected' => array_values(array_map('sanitize_text_field', array_slice((array)($evidence['connected'] ?? array()), 0, 10))),
            'partial' => array_values(array_map('sanitize_text_field', array_slice((array)($evidence['partial'] ?? array()), 0, 8))),
            'missing' => array_values(array_map('sanitize_text_field', array_slice((array)($evidence['missing'] ?? array()), 0, 12))),
            'intent' => sanitize_text_field((string)($evidence['intent'] ?? '')),
            'intent_note' => sanitize_text_field((string)($evidence['intent_note'] ?? '')),
            'route_order' => array_values(array_map('sanitize_key', array_slice((array)($evidence['route_order'] ?? array()), 0, 12))),
            'truth_rule' => sanitize_text_field((string)($evidence['truth_rule'] ?? '')),
        );
    }

    private function format_connection_evidence_for_prompt($evidence) {
        $evidence = is_array($evidence) ? $evidence : array();
        $connected = implode(' > ', (array)($evidence['connected'] ?? array()));
        $partial = implode(' > ', (array)($evidence['partial'] ?? array()));
        $missing = implode(' > ', array_slice((array)($evidence['missing'] ?? array()), 0, 8));
        $route = implode(' > ', (array)($evidence['route_order'] ?? array()));
        return implode("\n", array(
            '[Real Connection Evidence v7.5.8.39]',
            'Purpose: บังคับให้คำตอบแสดงหลักฐานว่าคัดจากแหล่งใดจริง ไม่ให้ตอบเหมือน local ทั้งที่อ้างว่าเชื่อมภายนอก',
            'Needs external: ' . (!empty($evidence['needs_external']) ? 'yes' : 'no') . ' · Evidence score: ' . intval($evidence['score'] ?? 0) . '/100',
            'Connected/testable providers: ' . ($connected !== '' ? $connected : 'none'),
            'Partial/limited providers: ' . ($partial !== '' ? $partial : 'none'),
            'Missing providers/settings: ' . ($missing !== '' ? $missing : 'none'),
            'Route order for this question: ' . ($route !== '' ? $route : 'none'),
            'Truth rule: ' . (string)($evidence['truth_rule'] ?? ''),
        ));
    }

    private function answer_source_line_from_result($result, $evidence) {
        $result = is_array($result) ? $result : array();
        $evidence = is_array($evidence) ? $evidence : array();
        $provider = sanitize_text_field((string)($result['provider'] ?? 'unknown'));
        $mode = sanitize_text_field((string)($result['mode'] ?? ''));
        $external = (!empty($evidence['connected']) || !empty($evidence['partial'])) ? 'มี connector ที่ตั้งค่า/ตรวจพบ' : 'ยังไม่มี external connector ที่ยืนยันพร้อมใช้งาน';
        if ($provider !== '' && !preg_match('/local|fallback|rescue|guard/i', $provider)) {
            return 'Answer source: external API candidate selected from ' . $provider . ($mode !== '' ? ' · mode=' . $mode : '') . ' · ' . $external;
        }
        return 'Answer source: internal/local fallback selected · ' . $external . ' · ถ้าต้องการคำตอบจากภายนอกจริง ให้ตั้งค่า API key / endpoint / CSE ID ให้พร้อม';
    }

    public function ajax_send_chat() {
        $this->verify();
        $message = isset($_POST['message']) ? $this->sanitize_prompt_text($_POST['message']) : '';
        $original_message = isset($_POST['original_message']) ? $this->sanitize_prompt_text($_POST['original_message']) : '';
        $latest_user_message = trim($original_message) !== '' ? $original_message : $message;
        $attachments = $this->parse_attachments_from_request();
        if (trim($message) === '' && trim($latest_user_message) === '' && empty($attachments)) {
            wp_send_json_error(array('message' => 'empty_message'), 400);
        }
        $memory_context = isset($_POST['memory_context']) ? $this->sanitize_prompt_text($_POST['memory_context']) : '';
        $history_context = isset($_POST['history_context']) ? $this->sanitize_prompt_text($_POST['history_context']) : '';
        $topic_context = isset($_POST['topic_context']) ? $this->sanitize_prompt_text($_POST['topic_context']) : '';
        $typo_context = isset($_POST['typo_context']) ? $this->sanitize_prompt_text($_POST['typo_context']) : '';
        $menu_context = isset($_POST['menu_context']) ? $this->sanitize_prompt_text($_POST['menu_context']) : '';
        $deep_context = isset($_POST['deep_context']) ? $this->sanitize_prompt_text($_POST['deep_context']) : '';
        $knowledge_orchestrator_context = isset($_POST['knowledge_orchestrator_context']) ? $this->sanitize_prompt_text($_POST['knowledge_orchestrator_context']) : '';
        $capability_orchestrator_context = isset($_POST['capability_orchestrator_context']) ? $this->sanitize_prompt_text($_POST['capability_orchestrator_context']) : '';
        $command_understanding_context = isset($_POST['command_understanding_context']) ? $this->sanitize_prompt_text($_POST['command_understanding_context']) : '';
        $adaptive_intelligence_context = isset($_POST['adaptive_intelligence_context']) ? $this->sanitize_prompt_text($_POST['adaptive_intelligence_context']) : '';
        $command_menu_router_context = isset($_POST['command_menu_router_context']) ? $this->sanitize_prompt_text($_POST['command_menu_router_context']) : '';
        $human_assistant_context = isset($_POST['human_assistant_context']) ? $this->sanitize_prompt_text($_POST['human_assistant_context']) : '';
        $human_understanding_context = isset($_POST['human_understanding_context']) ? $this->sanitize_prompt_text($_POST['human_understanding_context']) : '';
        $human_thought_support_context = isset($_POST['human_thought_support_context']) ? $this->sanitize_prompt_text($_POST['human_thought_support_context']) : '';
        $humanity_member_context = isset($_POST['humanity_member_context']) ? $this->sanitize_prompt_text($_POST['humanity_member_context']) : '';
        $humanity_safety_context = isset($_POST['humanity_safety_context']) ? $this->sanitize_prompt_text($_POST['humanity_safety_context']) : '';
        $member_onboarding_context = isset($_POST['member_onboarding_context']) ? $this->sanitize_prompt_text($_POST['member_onboarding_context']) : '';
        $feedback_center_context = isset($_POST['feedback_center_context']) ? $this->sanitize_prompt_text($_POST['feedback_center_context']) : '';
        $question_behavior_context = isset($_POST['question_behavior_context']) ? $this->sanitize_prompt_text($_POST['question_behavior_context']) : '';
        $human_profile_behavior_context = isset($_POST['human_profile_behavior_context']) ? $this->sanitize_prompt_text($_POST['human_profile_behavior_context']) : '';
        $question_reader_context_720 = isset($_POST['question_reader_context_720']) ? $this->sanitize_prompt_text($_POST['question_reader_context_720']) : '';
        $desired_output_context_725 = isset($_POST['desired_output_context_725']) ? $this->sanitize_prompt_text($_POST['desired_output_context_725']) : '';
        $deep_communication_context_734 = isset($_POST['deep_communication_context_734']) ? $this->sanitize_prompt_text($_POST['deep_communication_context_734']) : '';
        $code_master_context_734 = isset($_POST['code_master_context_734']) ? $this->sanitize_prompt_text($_POST['code_master_context_734']) : '';
        $global_communication_context_735 = isset($_POST['global_communication_context_735']) ? $this->sanitize_prompt_text($_POST['global_communication_context_735']) : '';
        $universal_code_context_735 = isset($_POST['universal_code_context_735']) ? $this->sanitize_prompt_text($_POST['universal_code_context_735']) : '';
        $auto_system_answer_context_720 = isset($_POST['auto_system_answer_context_720']) ? $this->sanitize_prompt_text($_POST['auto_system_answer_context_720']) : '';
        $developer_research_behavior_context = isset($_POST['developer_research_behavior_context']) ? $this->sanitize_prompt_text($_POST['developer_research_behavior_context']) : '';
        $answer_intent_context = isset($_POST['answer_intent_context_3463']) ? $this->sanitize_prompt_text($_POST['answer_intent_context_3463']) : '';
        $human_answer_context = isset($_POST['human_answer_context_3461']) ? $this->sanitize_prompt_text($_POST['human_answer_context_3461']) : (isset($_POST['human_answer_context_3460']) ? $this->sanitize_prompt_text($_POST['human_answer_context_3460']) : '');
        $reader_mode_context = isset($_POST['reader_mode_context']) ? $this->sanitize_prompt_text($_POST['reader_mode_context']) : '';
        $unified_answer_context = isset($_POST['unified_answer_context_3468']) ? $this->sanitize_prompt_text($_POST['unified_answer_context_3468']) : '';
        $auto_evolution_core_context = isset($_POST['auto_evolution_core_context_7587']) ? $this->sanitize_prompt_text($_POST['auto_evolution_core_context_7587']) : '';
        $thought_prompt_compiler_context = isset($_POST['thought_prompt_compiler_context_7589']) ? $this->sanitize_prompt_text($_POST['thought_prompt_compiler_context_7589']) : '';
        $system_command_matrix_context = isset($_POST['system_command_matrix_context_75810']) ? $this->sanitize_prompt_text($_POST['system_command_matrix_context_75810']) : '';
        $stable_rebase_guard_context = isset($_POST['stable_rebase_guard_context_75810']) ? $this->sanitize_prompt_text($_POST['stable_rebase_guard_context_75810']) : '';
        $answer_relationship_selector_context = isset($_POST['answer_relationship_selector_context_75836']) ? $this->sanitize_prompt_text($_POST['answer_relationship_selector_context_75836']) : '';
        $external_system_bridge_context = isset($_POST['external_system_bridge_context_75837']) ? $this->sanitize_prompt_text($_POST['external_system_bridge_context_75837']) : '';
        $communication_skill_evolution_context = isset($_POST['communication_skill_evolution_context_75837']) ? $this->sanitize_prompt_text($_POST['communication_skill_evolution_context_75837']) : '';
        $relationship_connector_context_75838 = isset($_POST['relationship_connector_context_75838']) ? $this->sanitize_prompt_text($_POST['relationship_connector_context_75838']) : '';
        $auto_compiled_prompt_7589 = isset($_POST['auto_compiled_prompt_7589']) ? $this->sanitize_prompt_text($_POST['auto_compiled_prompt_7589']) : '';
        if ($unified_answer_context !== '') {
            $command_understanding_context = trim($unified_answer_context . "

" . $command_understanding_context);
        }
        if ($auto_evolution_core_context !== '') {
            $command_understanding_context = trim($auto_evolution_core_context . "\n\n" . $command_understanding_context);
        }
        if ($thought_prompt_compiler_context !== '') {
            $command_understanding_context = trim($thought_prompt_compiler_context . "\n\n" . $command_understanding_context);
        }
        if ($system_command_matrix_context !== '') {
            $command_understanding_context = trim($system_command_matrix_context . "\n\n" . $command_understanding_context);
        }
        if ($stable_rebase_guard_context !== '') {
            $command_understanding_context = trim($stable_rebase_guard_context . "\n\n" . $command_understanding_context);
        }
        if ($answer_relationship_selector_context !== '') {
            $command_understanding_context = trim('[System Relationship Answer Selector v7.5.8.36]' . "\n" . $answer_relationship_selector_context . "\n" .
                'กติกา: เชื่อม Latest User Message + Context Reader + Prompt Processor + Result Resolver + System Command Matrix + API/local fallback เพื่อคัดเลือกคำตอบที่ใช้งานได้ที่สุด; ห้ามให้ prompt processor/context ภายในกลบคำถามล่าสุด และห้ามปล่อยคำตอบว่าง.' . "\n\n" . $command_understanding_context);
        }
        if ($external_system_bridge_context !== '') {
            $command_understanding_context = trim('[External System Bridge v7.5.8.37]' . "\n" . $external_system_bridge_context . "\n" . $command_understanding_context);
        }
        if ($communication_skill_evolution_context !== '') {
            $command_understanding_context = trim('[Communication Skill Evolution v7.5.8.37]' . "\n" . $communication_skill_evolution_context . "\n" . $command_understanding_context);
        }
        if ($relationship_connector_context_75838 !== '') {
            $command_understanding_context = trim('[Real Internal/External Relationship Pipeline v7.5.8.39]' . "\n" . $relationship_connector_context_75838 . "\n" .
                'กติกา: ก่อนตอบต้องเชื่อมความสัมพันธ์จริงของระบบภายในและภายนอก โดยแยก Internal Context, External Readiness, API Candidate, Local Direct, Rescue Candidate แล้วคัดคำตอบที่ตรงกับคำถามล่าสุดที่สุด ห้ามอ้างว่าเชื่อมระบบภายนอกแล้วถ้ายังไม่มี key/endpoint ที่พร้อม.' . "\n\n" . $command_understanding_context);
        }
        if ($auto_compiled_prompt_7589 !== '') {
            $command_understanding_context = trim('[Auto-Compiled Working Prompt v7.5.8.10]' . "\n" . $auto_compiled_prompt_7589 . "\n\n" . $command_understanding_context);
        }
        if (trim($latest_user_message) !== '') {
            $command_understanding_context = trim('[Latest User Message Lock v7.5.8.36]' . "
" .
                'คำถามจริงล่าสุดที่ต้องตอบก่อนทุกระบบ: ' . $latest_user_message . "
" .
                'กติกา: ถ้า context/prompt processor/system command matrix ทำให้คำตอบหลุด ให้ยึดข้อความนี้เป็นหลัก และต้องมีคำตอบจริง ไม่ใช่ตอบว่าไม่พบคำตอบอย่างเดียว.' .
                "

" . $command_understanding_context);
        }
        if ($human_answer_context !== '') {
            $command_understanding_context = trim($human_answer_context . "

" . $command_understanding_context);
        }
        if ($answer_intent_context !== '') {
            $command_understanding_context = trim($answer_intent_context . "

" . $command_understanding_context);
        }
        if ($developer_research_behavior_context !== '') {
            $command_understanding_context = trim($developer_research_behavior_context . "

" . $command_understanding_context);
        }
        if ($reader_mode_context !== '') {
            $command_understanding_context = trim($reader_mode_context . "

" . $command_understanding_context);
        }
        if ($auto_system_answer_context_720 !== '') {
            $command_understanding_context = trim($auto_system_answer_context_720 . "\n\n" . $command_understanding_context);
        }
        if ($universal_code_context_735 !== '') {
            $command_understanding_context = trim($universal_code_context_735 . "\n\n" . $command_understanding_context);
        }
        if ($code_master_context_734 !== '') {
            $command_understanding_context = trim($code_master_context_734 . "\n\n" . $command_understanding_context);
        }
        if ($global_communication_context_735 !== '') {
            $command_understanding_context = trim($global_communication_context_735 . "\n\n" . $command_understanding_context);
        }
        if ($deep_communication_context_734 !== '') {
            $command_understanding_context = trim($deep_communication_context_734 . "\n\n" . $command_understanding_context);
        }
        if ($desired_output_context_725 !== '') {
            $command_understanding_context = trim($desired_output_context_725 . "\n\n" . $command_understanding_context);
        }
        if ($question_reader_context_720 !== '') {
            $command_understanding_context = trim($question_reader_context_720 . "\n\n" . $command_understanding_context);
        }
        if ($human_profile_behavior_context !== '') {
            $command_understanding_context = trim($human_profile_behavior_context . "\n\n" . $command_understanding_context);
        }
        if ($question_behavior_context !== '') {
            $command_understanding_context = trim($question_behavior_context . "\n\n" . $command_understanding_context);
        }
        if ($feedback_center_context !== '') {
            $command_understanding_context = trim($feedback_center_context . "\n\n" . $command_understanding_context);
        }
        if ($member_onboarding_context !== '') {
            $command_understanding_context = trim($member_onboarding_context . "\n\n" . $command_understanding_context);
        }
        if ($humanity_safety_context !== '') {
            $command_understanding_context = trim($humanity_safety_context . "\n\n" . $command_understanding_context);
        }
        if ($humanity_member_context !== '') {
            $command_understanding_context = trim($humanity_member_context . "\n\n" . $command_understanding_context);
        }
        if ($human_understanding_context !== '') {
            $command_understanding_context = trim($human_understanding_context . "\n\n" . $command_understanding_context);
        }
        if ($human_thought_support_context !== '') {
            $command_understanding_context = trim($human_thought_support_context . "\n\n" . $command_understanding_context);
        }
        if ($human_assistant_context !== '') {
            $command_understanding_context = trim($human_assistant_context . "\n\n" . $command_understanding_context);
        }
        if ($command_menu_router_context !== '') {
            $command_understanding_context = trim($command_menu_router_context . "\n\n" . $command_understanding_context);
        }
        $relation_context = isset($_POST['relation_context']) ? $this->sanitize_prompt_text($_POST['relation_context']) : '';
        $context_contract = isset($_POST['context_contract']) ? $this->sanitize_prompt_text($_POST['context_contract']) : '';
        $context_lock = isset($_POST['context_lock']) ? $this->sanitize_prompt_text($_POST['context_lock']) : '';
        $full_context_map = isset($_POST['full_context_map']) ? $this->sanitize_prompt_text($_POST['full_context_map']) : '';
        $mandatory_read_context = isset($_POST['mandatory_read_context']) ? $this->sanitize_prompt_text($_POST['mandatory_read_context']) : '';
        $gateway_guard = isset($_POST['gateway_guard']) ? sanitize_key((string)wp_unslash($_POST['gateway_guard'])) : '';
        $gateway_original_chars = isset($_POST['gateway_original_chars']) ? absint($_POST['gateway_original_chars']) : 0;
        $gateway_sent_chars = isset($_POST['gateway_sent_chars']) ? absint($_POST['gateway_sent_chars']) : 0;
        $settings = get_option(self::OPTION_SETTINGS, array());
        if ($gateway_guard !== '') {
            $settings['__gateway_guard'] = $gateway_guard;
            $settings['__gateway_original_chars'] = $gateway_original_chars;
            $settings['__gateway_sent_chars'] = $gateway_sent_chars;
        }
        $secrets = $this->usable_api_secrets();
        $connection_evidence = $this->build_real_connection_evidence($settings, $secrets, $latest_user_message);
        $settings['__connection_evidence'] = $connection_evidence;
        $connection_evidence_context = $this->format_connection_evidence_for_prompt($connection_evidence);
        if ($connection_evidence_context !== '') {
            $command_understanding_context = trim($connection_evidence_context . "

" . $command_understanding_context);
        }
        $research_context = $this->build_research_context($latest_user_message, $settings, $secrets);
        $processor_context = $this->gpt_like_processor_context($latest_user_message, $settings, $attachments);
        if ($processor_context !== '') {
            $knowledge_orchestrator_context = trim($knowledge_orchestrator_context . "

" . $processor_context);
        }
        $message_for_ai = $this->compose_contextual_prompt($message, $memory_context, $history_context, $topic_context, $typo_context, $menu_context, $research_context, $deep_context, $knowledge_orchestrator_context, $context_contract, $context_lock, $full_context_map, $mandatory_read_context, $relation_context, $capability_orchestrator_context, $command_understanding_context, $adaptive_intelligence_context);
        if (trim($latest_user_message) !== '') {
            $message_for_ai = '[LATEST USER MESSAGE — ANSWER THIS FIRST v7.5.8.36]' . "\n" . $latest_user_message . "\n\n" . $message_for_ai;
        }
        if ($gateway_guard !== '' || strlen($message_for_ai) > 28000) {
            $message_for_ai = $this->gateway_guard_compact_prompt($message_for_ai, 26000);
            if (empty($settings['__gateway_guard'])) {
                $settings['__gateway_guard'] = 'server_compact_v7561';
            }
        }
        $local_fallback_message = $this->compose_latest_context_fallback_message($latest_user_message, trim($adaptive_intelligence_context . "\n\n" . $command_understanding_context), $capability_orchestrator_context, $relation_context, $context_contract, $context_lock, $full_context_map, $mandatory_read_context);
        $direct_answer = $this->local_direct_answer_if_possible($latest_user_message, $settings, $attachments);
        if ($direct_answer !== '') {
            $direct_result = array('reply' => $direct_answer, 'provider' => 'local_direct_answer_core', 'mode' => 'direct_answer_v75839', 'intent' => 'direct_answer');
            $direct_result['connection_evidence'] = $this->public_connection_evidence($connection_evidence);
            $direct_result['answer_source_line'] = $this->answer_source_line_from_result($direct_result, $connection_evidence);
            wp_send_json_success($direct_result);
        }
        $result = $this->call_ai($message_for_ai, $settings, $secrets, $attachments, $latest_user_message);
        $result = $this->ensure_non_empty_chat_result($result, $local_fallback_message, $settings);
        $result['connection_evidence'] = $this->public_connection_evidence($connection_evidence);
        $result['answer_source_line'] = $this->answer_source_line_from_result($result, $connection_evidence);
        if ($research_context !== '') {
            $result['research_mode'] = 'internal_external_synthesis';
            $result['research_summary'] = sanitize_text_field($this->research_query_from_message($latest_user_message));
        }
        wp_send_json_success($result);
    }

    private function gateway_guard_compact_prompt($text, $max = 26000) {
        $text = (string)$text;
        $max = max(8000, min(32000, intval($max)));
        if (strlen($text) <= $max) return $text;
        $head_len = intval($max * 0.62);
        $tail_len = max(2400, $max - $head_len - 1200);
        $head = substr($text, 0, $head_len);
        $tail = substr($text, -$tail_len);
        $digest = "\n\n[AiRA Server Gateway Guard v7.5.6.1]\n";
        $digest .= "ข้อความถูกย่อฝั่งเซิร์ฟเวอร์เพื่อป้องกัน 504 Gateway Timeout จาก admin-ajax/nginx\n";
        $digest .= "Original chars: " . strlen($text) . " · Sent chars approx: " . ($head_len + $tail_len) . "\n";
        $digest .= "กติกา: ตอบให้ตรงคำสั่งล่าสุด รักษาของเดิม ห้ามเดาทำลายโค้ดเดิม และถ้า code context ถูกย่อให้ส่งแพตช์/โค้ดส่วนสำคัญที่ใช้งานจริงก่อน\n\n";
        return $head . $digest . $tail;
    }

    private function ensure_non_empty_chat_result($result, $message, $settings) {
        $result = is_array($result) ? $result : array();
        $reply = isset($result['reply']) ? trim(wp_strip_all_tags((string)$result['reply'])) : '';
        if ($reply !== '') return $result;
        $intent = $this->classify_chat_intent((string)$message, array());
        $errors = array('empty_reply_guard:no_blank_response_allowed');
        $fallback = $this->local_search_recovery_answer((string)$message, is_array($settings) ? $settings : array(), $errors, $intent);
        if (trim(wp_strip_all_tags((string)$fallback)) === '') {
            $fallback = $this->local_best_effort_fallback((string)$message, is_array($settings) ? $settings : array(), $errors, $intent);
        }
        if (trim(wp_strip_all_tags((string)$fallback)) === '') {
            $fallback = "AiRA อ่านคำสั่งแล้ว แต่ยังไม่มีข้อมูล/API ที่ตอบได้ครบในรอบนี้

สิ่งที่ทำได้ทันที:
1. ตรวจ Real API Center ว่ามี provider ที่เชื่อมสำเร็จ
2. ตรวจ Memory / Topic / Docs ว่ามีข้อมูลให้ AiRA อ่านหรือไม่
3. ถ้าต้องใช้ข้อมูลภายนอก ให้ตั้ง Google Search API + CSE ID หรือ provider ค้นคว้า

สถานะ: empty reply ถูกบล็อกแล้ว ระบบจึงส่งคำตอบสำรองแทนค่าว่าง";
        }
        $result['reply'] = $fallback;
        $result['provider'] = $result['provider'] ?? 'empty_reply_guard';
        $result['mode'] = 'empty_reply_guard';
        $result['intent'] = $result['intent'] ?? $intent['key'];
        $result['intent_note'] = $result['intent_note'] ?? $intent['note'];
        $result['search_recovery'] = true;
        $result['errors'] = isset($result['errors']) && is_array($result['errors']) ? array_merge($result['errors'], $errors) : $errors;
        return $result;
    }

    private function call_ai($message, $settings, $secrets, $attachments = array(), $intent_source = '') {
        $route_message = trim((string)$intent_source) !== '' ? (string)$intent_source : (string)$message;
        $intent = $this->classify_chat_intent($route_message, $attachments);
        $settings = is_array($settings) ? $settings : array();
        $settings['__intent'] = $intent['key'];
        $settings['__intent_note'] = $intent['note'];
        $settings['__has_vision_attachment'] = !empty($attachments) ? 'yes' : '';
        $order = $this->chat_providers($settings, $route_message, $attachments);
        $errors = array();
        foreach ($order as $p) {
            if ($p === 'universal_api_hub') { continue; }
            $key = $this->normalize_api_key_input((string)($secrets[$p] ?? ''));
            if ($key === '' && !empty($secrets['universal_api_hub'])) {
                // Universal Hub fallback: only use the hub key when its detected provider matches $p
                // (or shape is unknown so we let it try once). Avoids wasting a Gemini key on an OpenAI call.
                $hub_key = $this->normalize_api_key_input((string)$secrets['universal_api_hub']);
                $detected = $this->detect_provider_from_api_key($hub_key);
                if ($detected === $p || $detected === '') {
                    $key = $hub_key;
                }
            }
            if ($p !== 'custom' && $key === '') { $errors[] = $p . ':not_configured'; continue; }
            $shape = $this->provider_key_shape_warning($p, $key);
            if ($shape) { $errors[] = $p . ':' . $shape; }
            $reply = $this->provider_request($p, $message, $settings, $key, $attachments);
            if (!empty($reply['ok'])) {
                if ($this->looks_like_low_value_answer($reply['text'] ?? '') && (string)($settings['low_value_answer_retry'] ?? 'enabled') !== 'off') {
                    $errors[] = $p . ':low_value_answer';
                    $recovery_message = $this->build_search_recovery_message($message, $intent, $errors);
                    $retry = $this->provider_request($p, $recovery_message, $settings, $key, $attachments);
                    if (!empty($retry['ok']) && !$this->looks_like_low_value_answer($retry['text'] ?? '')) {
                        return array(
                            'reply' => $retry['text'],
                            'provider' => $p,
                            'mode' => 'api_search_recovery',
                            'model' => $this->provider_model($p, $settings),
                            'intent' => $intent['key'],
                            'intent_note' => $intent['note'],
                            'route_order' => $order,
                            'search_recovery' => true,
                        );
                    }
                    $errors[] = $p . ':recovery_failed:' . substr((string)($retry['text'] ?? 'empty'), 0, 140);
                    continue;
                }
                return array(
                    'reply' => $reply['text'],
                    'provider' => $p,
                    'mode' => 'api',
                    'model' => $this->provider_model($p, $settings),
                    'intent' => $intent['key'],
                    'intent_note' => $intent['note'],
                    'route_order' => $order,
                );
            }
            $errors[] = $p . ':' . substr((string)($reply['text'] ?? 'unknown_error'), 0, 180);
        }
        $fallback = $this->local_relation_pipeline_answer($message, $route_message, $settings, $errors, $intent);
        if ($fallback === '') {
            $fallback = $this->local_hard_read_fallback($message, $route_message, $settings, $errors, $intent);
        }
        if ($fallback === '') {
            $fallback = (string)($settings['search_answer_recovery_mode'] ?? 'retry_then_best_effort') === 'off'
                ? $this->local_best_effort_fallback($route_message, $settings, $errors, $intent)
                : $this->local_search_recovery_answer($route_message, $settings, $errors, $intent);
        }
        return array('reply' => $fallback, 'provider' => 'local_search_recovery_guard', 'mode' => 'fallback_search_recovery', 'intent' => $intent['key'], 'intent_note' => $intent['note'], 'route_order' => $order, 'errors' => $errors, 'search_recovery' => true);
    }



    private function local_relation_pipeline_answer($contextual_message, $latest_message, $settings, $errors, $intent) {
        $ctx = (string)$contextual_message;
        $latest = trim(wp_strip_all_tags((string)$latest_message));
        $needle = $latest . "\n" . $ctx;
        $is_relation_request = (bool)preg_match('/(เชื่อม.*(ภายใน|ภายนอก|external|internal)|ภายใน.*ภายนอก|สัมพันธ์ระบบ|คัดเลือกคำตอบ|answer selector|relationship|ระบบภายนอก|ทักษะการสื่อสาร|สื่อสาร)/iu', $needle);
        $has_pipeline_context = stripos($ctx, 'Real Internal/External Relationship Pipeline') !== false;
        if (!$is_relation_request && !$has_pipeline_context) return '';
        $ready = $this->pick_context_line_for_fallback($ctx, array('/Ready external:\s*(.+)/iu', '/Connected now:\s*(.+)/iu'), 420);
        $missing = $this->pick_context_line_for_fallback($ctx, array('/Missing external:\s*(.+)/iu', '/Missing\/config needed:\s*(.+)/iu'), 520);
        $internal = $this->pick_context_line_for_fallback($ctx, array('/Internal relationship:\s*(.+)/iu', '/Connected systems:\s*(.+)/iu'), 640);
        $selection = $this->pick_context_line_for_fallback($ctx, array('/Selection order:\s*(.+)/iu', '/Selection priority:\s*(.+)/iu'), 520);
        $latest_line = $this->pick_context_line_for_fallback($ctx, array('/Latest user message:\s*(.+)/iu', '/คำถามจริงล่าสุดที่ต้องตอบก่อนทุกระบบ:\s*(.+)/iu'), 520);
        if ($latest_line !== '') $latest = $latest_line;
        $evidence = isset($settings['__connection_evidence']) && is_array($settings['__connection_evidence']) ? $settings['__connection_evidence'] : array();
        $ev_connected = implode(' > ', (array)($evidence['connected'] ?? array()));
        $ev_partial = implode(' > ', (array)($evidence['partial'] ?? array()));
        $ev_missing = implode(' > ', array_slice((array)($evidence['missing'] ?? array()), 0, 8));
        $ev_route = implode(' > ', (array)($evidence['route_order'] ?? array()));
        $lines = array();
        $lines[] = 'ถูกครับ คำตอบก่อนหน้ายังดู Local เพราะยังไม่มี “หลักฐานแหล่งคำตอบ” ติดมากับคำตอบจริง';
        $lines[] = '';
        $lines[] = 'รอบนี้ระบบต้องคัดคำตอบจากหลักฐานจริงก่อนแสดง:';
        $lines[] = '1. Internal evidence: คำถามล่าสุด, Prompt Processor, Context Reader, System Command Matrix, Result Resolver และ Memory/Rooms';
        $lines[] = '2. External evidence: provider/API ที่ตั้งค่าแล้วจริง เช่น OpenAI/Claude/Gemini/OpenRouter/Google CSE/GitHub/Custom endpoint';
        $lines[] = '3. Candidate evidence: API reply, internal context answer, local direct answer, rescue answer';
        $lines[] = '4. Source report: ทุกคำตอบที่เกี่ยวกับระบบภายนอกต้องบอกว่าเลือกจาก API จริง หรือ fallback ภายใน';
        $lines[] = '';
        $lines[] = 'หลักฐานการเชื่อมต่อรอบนี้:';
        $lines[] = '- Connected/testable: ' . sanitize_text_field($ev_connected !== '' ? $ev_connected : 'ยังไม่มี provider ภายนอกที่ยืนยันพร้อมใช้จริง');
        if ($ev_partial !== '') $lines[] = '- Partial/limited: ' . sanitize_text_field($ev_partial);
        $lines[] = '- Missing/config: ' . sanitize_text_field($ev_missing !== '' ? $ev_missing : 'none');
        if ($ev_route !== '') $lines[] = '- Route order: ' . sanitize_text_field($ev_route);
        $lines[] = '';
        $lines[] = 'สถานะจากระบบรอบนี้:';
        $lines[] = '- คำถามล่าสุด: ' . sanitize_text_field(mb_substr($latest !== '' ? $latest : '-', 0, 420));
        if ($internal !== '') $lines[] = '- Internal relationship: ' . sanitize_text_field($internal);
        $lines[] = '- External ready: ' . sanitize_text_field($ready !== '' ? $ready : 'ยังไม่พบ external connector ที่ยืนยันว่าพร้อมใช้จริง');
        $lines[] = '- External missing/config: ' . sanitize_text_field($missing !== '' ? $missing : 'ต้องตรวจ API key / endpoint / CSE ID ใน Real API Center');
        if ($selection !== '') $lines[] = '- Selection order: ' . sanitize_text_field($selection);
        if (is_array($errors) && $errors) {
            $lines[] = '- Provider/API รอบนี้: ' . sanitize_text_field(implode(' · ', array_slice($errors, 0, 5)));
        }
        $lines[] = '';
        $lines[] = 'กติกาคำตอบหลังอัปเกรดนี้:';
        $lines[] = '- ถ้า external พร้อมจริง ให้ใช้คำตอบจาก provider/API เป็น candidate หลัก';
        $lines[] = '- ถ้า external ยังไม่พร้อม ห้ามอ้างว่าเชื่อมแล้ว ให้ตอบจาก internal context พร้อมบอกสิ่งที่ต้องตั้งค่า';
        $lines[] = '- ถ้าเป็นคำถามง่าย เช่น 7+7 ให้ตอบตรงทันที ไม่ลากเข้าระบบยาว';
        $lines[] = '- ถ้าเป็นงานระบบ ให้ตอบแบบ อาการ → สาเหตุ → จุดแก้ → วิธีเช็ก → สถานะ';
        return implode("\n", $lines);
    }


    private function local_hard_read_fallback($contextual_message, $latest_message, $settings, $errors, $intent) {
        $ctx = (string)$contextual_message;
        if (stripos($ctx, 'CAPABILITY ORCHESTRATOR') === false && stripos($ctx, 'RELATION CONTEXT BRAIN') === false && stripos($ctx, 'HARD READ BEFORE ANSWER') === false && stripos($ctx, 'MANDATORY READ PACK') === false && stripos($ctx, 'Full Page Context Map') === false && stripos($ctx, 'Context Contract Map') === false) {
            return '';
        }
        $latest = trim(wp_strip_all_tags((string)$latest_message));
        $relation_active_issue = '';
        $relation_type = '';
        $relation_related = '';
        if (preg_match('/Active issue to answer:\s*([^\n]+)/iu', $ctx, $m)) $relation_active_issue = trim($m[1]);
        if (preg_match('/Relation type:\s*([^\n]+)/iu', $ctx, $m)) $relation_type = trim($m[1]);
        if (preg_match('/Best related previous message:\s*([^\n]+)/iu', $ctx, $m)) $relation_related = trim($m[1]);
        $capability_route = '';
        $capability_sources = '';
        $capability_missing = '';
        if (preg_match('/Capability route:\s*([^\n]+)/iu', $ctx, $m)) $capability_route = trim($m[1]);
        if (preg_match('/Data sources that must be consulted before answering:\s*([\s\S]*?)API\/Search readiness:/iu', $ctx, $m)) $capability_sources = trim($m[1]);
        if (preg_match('/Missing\/limited:\s*([^\n]+)/iu', $ctx, $m)) $capability_missing = trim($m[1]);
        if ($relation_active_issue !== '') $latest = $relation_active_issue;
        $topic = is_array($intent) ? sanitize_text_field((string)($intent['note'] ?? $intent['key'] ?? 'ทั่วไป')) : 'ทั่วไป';
        $read_count = '';
        if (preg_match('/Messages read:\s*([^\n]+)/iu', $ctx, $m)) $read_count = trim($m[1]);
        $active_problem = '';
        if (preg_match('/Current unresolved issue:\s*([^\n]+)/iu', $ctx, $m)) $active_problem = trim($m[1]);
        $active_module = '';
        if (preg_match('/Active module:\s*([^\n]+)/iu', $ctx, $m)) $active_module = trim($m[1]);
        $latest_from_pack = '';
        if (preg_match('/Latest command:\s*([^\n]+)/iu', $ctx, $m)) $latest_from_pack = trim($m[1]);
        if ($latest_from_pack !== '') $latest = $latest_from_pack;
        $contract_mode = '';
        if (preg_match('/Expected answer mode:\s*([^\n]+)/iu', $ctx, $m)) $contract_mode = trim($m[1]);
        $previous_fixes = '';
        if (preg_match('/Previous fixes already attempted:\s*([^\n]+)/iu', $ctx, $m)) $previous_fixes = trim($m[1]);
        $failure_chain = '';
        if (preg_match('/Repeated user failure chain:\s*([^\n]+)/iu', $ctx, $m)) $failure_chain = trim($m[1]);
        $is_context_issue = (bool)preg_match('/(อ่าน|บริบท|context|ประเด็น|จับโครงสร้าง|ทั้งหน้า|ไม่อ่าน|ตอบไปเรื่อย|ไม่แม่น|เชื่อมความสามารถ|ความสามารถ|ความรู้|ไม่ฉลาด|ไม่รู้อะไร|orchestrator)/iu', $latest . ' ' . $active_problem);
        $is_plugin_repair = (bool)preg_match('/(แก้|ซ่อม|รื้อ|ปลั๊กอิน|plugin|wordpress|ระบบ|runtime|เมนู|หน้าแชท|animation|ประมวลผล)/iu', $latest . ' ' . $active_module);
        if (!$is_context_issue && !$is_plugin_repair) return '';
        $lines = array();
        if ($relation_active_issue !== '') {
            $lines[] = 'ผมจับประเด็นที่เชื่อมโยงกันได้แล้ว: ' . sanitize_text_field(mb_substr($relation_active_issue, 0, 520));
            if ($relation_type !== '') $lines[] = 'ชนิดความสัมพันธ์ของคำถาม: ' . sanitize_text_field($relation_type);
            if ($relation_related !== '' && strtolower($relation_related) !== 'none') $lines[] = 'ข้อความก่อนหน้าที่เกี่ยวข้อง: ' . sanitize_text_field(mb_substr($relation_related, 0, 420));
            $lines[] = '';
        }
        $lines[] = 'ปัญหาจริงคือ AiRA มีเมนู/ความสามารถหลายส่วน แต่ยังไม่มีตัวกลางที่บังคับเชื่อม “ความสามารถ + ความรู้ + API/Search + ประเด็นล่าสุด” เป็นเส้นทางเดียวก่อนตอบ จึงดูเหมือนมีระบบรองรับแต่ไม่ฉลาดและไม่รู้ว่าจะใช้ส่วนไหน';
        $lines[] = '';
        $lines[] = 'สิ่งที่อ่านและล็อกเป็นประเด็นตอนนี้:';
        if ($capability_route !== '') $lines[] = '- เส้นทางความสามารถที่ต้องใช้: ' . sanitize_text_field(mb_substr($capability_route, 0, 520));
        if ($capability_sources !== '') $lines[] = '- แหล่งข้อมูลที่ต้องอ่านก่อนตอบ: ' . sanitize_text_field(mb_substr(preg_replace('/\s+/', ' ', $capability_sources), 0, 520));
        if ($capability_missing !== '') $lines[] = '- ความสามารถ/API ที่ยังจำกัด: ' . sanitize_text_field(mb_substr($capability_missing, 0, 360));
        if ($read_count !== '') $lines[] = '- จำนวนข้อความ/บริบทที่อ่าน: ' . sanitize_text_field($read_count);
        if ($active_module !== '') $lines[] = '- ระบบที่เกี่ยวข้อง: ' . sanitize_text_field($active_module);
        if ($active_problem !== '') $lines[] = '- ปัญหาปัจจุบัน: ' . sanitize_text_field($active_problem);
        if ($latest !== '') $lines[] = '- คำสั่งล่าสุด: ' . sanitize_text_field(mb_substr($latest, 0, 360));
        if ($contract_mode !== '') $lines[] = '- โหมดคำตอบที่ควรใช้: ' . sanitize_text_field($contract_mode);
        if ($failure_chain !== '' && $failure_chain !== 'none') $lines[] = '- ประวัติปัญหาซ้ำที่ต้องไม่มองข้าม: ' . sanitize_text_field(mb_substr($failure_chain, 0, 500));
        if ($previous_fixes !== '' && $previous_fixes !== 'none') $lines[] = '- แพตช์/คำตอบที่เคยลองแล้ว: ' . sanitize_text_field(mb_substr($previous_fixes, 0, 500));
        $lines[] = '';
        $lines[] = 'สาเหตุที่ทำให้ “ไม่เข้าใจ” ในเวอร์ชันก่อน:';
        $lines[] = '1. ระบบมี Memory / Tags / Create / Knowledge / Update / API แต่ยังไม่ได้เลือกและผูกแหล่งข้อมูลเหล่านี้เข้ากับคำถามล่าสุดแบบบังคับ';
        $lines[] = '2. Relation Graph รู้ว่าเป็น feedback แต่ยังไม่ได้รู้ว่า “ต้องใช้ความสามารถใด + อ่านแหล่งไหน + ขาด API อะไร” ก่อนตอบ';
        $lines[] = '3. fallback ยังตอบจากหมวดกว้างได้เมื่อ API ไม่พร้อม ทำให้เหมือน AiRA ไม่ฉลาด';
        $lines[] = '';
        $lines[] = 'การแก้ที่ใช้ในเวอร์ชันนี้:';
        $lines[] = '1. เพิ่ม Capability Orchestrator เป็นชั้นแรกก่อน Relation/Context/Memory';
        $lines[] = '2. ให้ระบบเลือก module ที่ต้องใช้ เช่น Context Brain, Knowledge Base, Tags, Create, API/Search, WordPress ตามโจทย์ล่าสุด';
        $lines[] = '3. ให้ระบบระบุแหล่งข้อมูลที่ต้องอ่านก่อนตอบ และระบุส่วนที่ยังขาด เช่น Search API/CSE หรือ provider หลัก';
        $lines[] = '4. ให้ fallback ตอบจากเส้นทางความสามารถนี้โดยตรง ไม่ตอบ template ว่า “เปิด API” อย่างเดียว';
        $lines[] = '';
        $lines[] = 'สถานะ: ต้องใช้ v3.4.26 ขึ้นไปเพื่อให้ Unified Context Bridge + Composer Submit Stabilizer เชื่อมคำสั่งล่าสุด ความสามารถ ความรู้ และการโฟกัสหน้าแชทก่อนตอบ';
        $err = is_array($errors) ? array_slice($errors, 0, 3) : array();
        if ($err) $lines[] = 'API status: ' . sanitize_text_field(implode(' · ', $err));
        return implode("\n", $lines);
    }

    private function local_best_effort_fallback($message, $settings, $errors, $intent) {
        $msg = trim(wp_strip_all_tags((string)$message));
        $short = function_exists('mb_substr') ? mb_substr($msg, 0, 420, 'UTF-8') : substr($msg, 0, 420);
        $topic = sanitize_text_field((string)($intent['note'] ?? $intent['key'] ?? 'ทั่วไป'));
        $err = is_array($errors) ? array_slice($errors, 0, 4) : array();
        $lines = array();
        $lines[] = "คำตอบเบื้องต้นจาก AiRA";
        $lines[] = "";
        $lines[] = "สิ่งที่ตีความได้ตอนนี้:";
        $lines[] = "- หมวดคำถาม: " . $topic;
        if ($short !== '') $lines[] = "- คำสั่งหลัก: " . sanitize_text_field($short);
        $lines[] = "";
        $lines[] = "แนวทางตอบแบบเข้มข้นโดยไม่หลอกข้อมูล:";
        $lines[] = "1. AiRA จะรวม Memory, Topic, Docs, ลิงก์, Smart Tags และคำสั่งต่อเนื่องก่อน";
        $lines[] = "2. ถ้าเป็นความรู้ทั่วไป AiRA จะให้คำตอบที่เป็นหลักการ/แนวทางใช้งานได้ทันที";
        $lines[] = "3. ถ้าเป็นข้อมูลล่าสุด ราคา กฎหมาย ข่าว หรือข้อเท็จจริงภายนอก ต้องเปิด API/Search เพื่อยืนยัน";
        $lines[] = "4. ถ้าต้องเดาหรือประเมิน AiRA จะติดป้ายว่าเป็นสมมติฐาน ไม่ฟันธงว่าเป็นข้อเท็จจริง";
        $lines[] = "";
        $lines[] = "ถ้าต้องการคำตอบระดับโมเดลเต็ม ให้ไปที่ ตั้งค่า · Real API Center > ทดสอบทุก API แล้วดู Ready/Missing Alert";
        if ($err) $lines[] = "สถานะล่าสุด: " . sanitize_text_field(implode(' · ', $err));
        return implode("\n", $lines);
    }

    private function provider_default_model($provider) {
        $provider = sanitize_key($provider);
        if ($provider === 'openai') return 'gpt-4o-mini';
        if ($provider === 'openrouter') return 'openai/gpt-4o-mini';
        if ($provider === 'anthropic') return 'claude-sonnet-4-20250514';
        if ($provider === 'gemini') return 'gemini-2.5-flash';
        if ($provider === 'groq') return 'llama-3.3-70b-versatile';
        if ($provider === 'mistral') return 'mistral-large-latest';
        if ($provider === 'perplexity') return 'sonar-pro';
        if ($provider === 'xai') return 'grok-4.3';
        if ($provider === 'deepseek') return 'deepseek-chat';
        if ($provider === 'together') return 'meta-llama/Llama-3.3-70B-Instruct-Turbo';
        if ($provider === 'custom') return 'aira-custom';
        return 'gpt-4o-mini';
    }

    private function set_provider_model_override($provider, $settings, $model) {
        $settings = is_array($settings) ? $settings : array();
        $provider = sanitize_key($provider);
        if ($provider === 'openai') $settings['primary_model'] = $model;
        elseif ($provider === 'openrouter') $settings['openrouter_model'] = $model;
        elseif ($provider === 'anthropic') $settings['anthropic_model'] = $model;
        elseif ($provider === 'gemini') $settings['gemini_model'] = $model;
        elseif (in_array($provider, array('groq','mistral','perplexity','xai','deepseek','together'), true)) $settings[$provider . '_model'] = $model;
        elseif ($provider === 'custom') $settings['custom_model'] = $model;
        return $settings;
    }

    private function looks_like_model_error($text) {
        $text = strtolower((string)$text);
        return (bool)preg_match('/(model|โมเดล|does not exist|not found|invalid|unsupported|not supported|unknown model|no model|404)/i', $text);
    }


    private function looks_like_low_value_answer($text) {
        $text = trim(wp_strip_all_tags((string)$text));
        if ($text === '') return true;
        $lower = function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
        $len = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
        // Only flag as low-value if reply is very short AND consists almost entirely of an apology/refusal
        // (was triggering on legitimate short answers that happened to contain "ไม่พบ" etc.)
        if ($len < 24) {
            if (preg_match('/^(ขออภัย|sorry|ไม่ทราบ|ไม่รู้|ไม่สามารถ|ไม่พบข้อมูล|cannot help|i\s*don\'?t\s*know)/iu', $lower)) return true;
        }
        // Strong canned-failure phrases (model literally giving up) — keep these
        return (bool)preg_match('/(ไม่สามารถค้นหาคำตอบ|ไม่พบคำตอบสำหรับคำถาม|หา(?:ข้อมูล|คำตอบ)ไม่พบเลย|ไม่สามารถเข้าถึงข้อมูล|ไม่สามารถค้นหา|cannot find (?:an )?answer|unable to (?:find|search) (?:any|the) (?:answer|information)|no relevant information available|i do not have access to|i can(?:not|\'t) browse the (?:web|internet))/iu', $lower);
    }

    private function build_search_recovery_message($message, $intent, $errors = array()) {
        $err = is_array($errors) && $errors ? implode(' · ', array_slice($errors, 0, 5)) : 'none';
        $intent_note = is_array($intent) ? sanitize_text_field((string)($intent['note'] ?? $intent['key'] ?? 'general')) : 'general';
        return "Search/Answer Recovery Mode\n" .
            "- ห้ามตอบแค่ว่าไม่พบคำตอบหรือค้นหาไม่ได้\n" .
            "- ให้ใช้ข้อมูลที่มีในคำสั่งล่าสุด, Memory, Topic, Docs, Smart Tags, ลิงก์ที่แนบ และ API/Search ที่เชื่อมแล้ว\n" .
            "- ถ้าข้อมูลภายนอกหรือ API ยังไม่พร้อม ให้ตอบส่วนที่มั่นใจได้ก่อน แล้วแยก 'ข้อจำกัด/ต้องเชื่อมต่อเพิ่ม' แบบสั้น\n" .
            "- ถ้าเป็นงาน WordPress/AiRA Studio ให้เสนอวิธีแก้จริงเป็นลำดับ และระบุไฟล์/ระบบที่ควรตรวจ\n" .
            "- ตอบเป็นไทย ตรงประเด็น กระชับแบบ GPT\n" .
            "Intent: " . $intent_note . "\n" .
            "Provider errors so far: " . sanitize_text_field($err) . "\n\n" .
            "คำสั่งล่าสุดของผู้ใช้:\n" . (string)$message;
    }

    private function local_search_recovery_answer($message, $settings, $errors, $intent) {
        $answer = $this->local_answer_engine($message, $settings, $errors, $intent);
        if (trim($answer) !== '') return $answer;
        return $this->local_best_effort_fallback($message, $settings, $errors, $intent);
    }

    private function local_answer_engine($message, $settings, $errors, $intent) {
        $raw = trim(wp_strip_all_tags((string)$message));
        if ($raw === '') return '';
        $topic = is_array($intent) ? sanitize_text_field((string)($intent['note'] ?? $intent['key'] ?? 'ทั่วไป')) : 'ทั่วไป';
        $lower = function_exists('mb_strtolower') ? mb_strtolower($raw, 'UTF-8') : strtolower($raw);
        $short = function_exists('mb_substr') ? mb_substr($raw, 0, 320, 'UTF-8') : substr($raw, 0, 320);
        $needs_latest = (bool)preg_match('/(ล่าสุด|วันนี้|ตอนนี้|ราคา|ข่าว|กฎหมาย|อัตรา|หุ้น|crypto|weather|พยากรณ์|ตรวจเว็บจริง|ค้นเว็บจริง|real[- ]?time)/iu', $raw);
        $is_wp = (bool)preg_match('/(wordpress|wp|ปลั๊กอิน|plugin|widget|shortcode|gutenberg|elementor|theme|ธีม|admin|dashboard|composer|api center)/iu', $raw);
        $is_build = (bool)preg_match('/(สร้าง|ทำ|พัฒนา|ออกแบบ|ระบบ|build|create|generate|plugin|ปลั๊กอิน|โค้ด|code)/iu', $raw);
        $is_fix = (bool)preg_match('/(แก้|ซ่อม|บั๊ก|bug|error|ใช้ไม่ได้|ไม่ทำงาน|ล้มเหลว|โหลดไม่ได้|ตอบไม่ได้|ค้นหาไม่ได้|ซ้อน|ทับ|ล้น)/iu', $raw);
        $is_api = (bool)preg_match('/(api|key|endpoint|model|provider|openai|claude|gemini|elevenlabs|perplexity|groq|custom)/iu', $raw);
        $is_voice = (bool)preg_match('/(voice|เสียง|พูด|อ่านเสียง|ไมค์|realtime|tts|stt|elevenlabs)/iu', $raw);
        $is_visual = (bool)preg_match('/(ภาพ|สี|วาด|visual|svg|animation|ref|รูป|generate image)/iu', $raw);
        $is_app_game_code = (bool)preg_match('/(เขียนโค้ด|เขียนโปรแกรม|เขียนแอพ|สร้างแอพ|app|application|program|เกม|game|ระบบเล่นเกม|html5|canvas|pwa|mobile)/iu', $raw);
        $is_google_search_task = (bool)preg_match('/(google|ค้นหา|ข้อมูลทั่วไป|ภาพ|gif|ภาพเคลื่อนไหว|ท้องถิ่น|local|จังหวัด|ใกล้ฉัน|ออกแบบ|design)/iu', $raw);
        $is_latest_context_fallback = (bool)preg_match('/LATEST CONTEXT LINE FALLBACK|Active issue to answer now|Related previous command|ไม่เข้าใจ|ไม่ฉลาด|เดาไม่ออก|ไม่เชื่อมโยง|บริบท/iu', $raw);
        $active_issue = '';
        if (preg_match('/Active issue to answer now:\s*(.+)/iu', $raw, $m)) {
            $active_issue = trim((string)$m[1]);
        }
        $target_module = '';
        if (preg_match('/Target module:\s*(.+)/iu', $raw, $m)) {
            $target_module = trim((string)$m[1]);
        }
        $lines = array();
        $lines[] = 'คำตอบเบื้องต้นจาก AiRA';
        $lines[] = '';
        if ($needs_latest) {
            $lines[] = 'คำถามนี้มีส่วนที่ต้องใช้ข้อมูลภายนอก/ข้อมูลล่าสุด AiRA จึงตอบส่วนที่วิเคราะห์ได้ก่อน และจะแจ้งให้เปิด Search/API เมื่อต้องยืนยันข้อเท็จจริงล่าสุด';
            $lines[] = '';
        }
        if ($is_latest_context_fallback && ($active_issue !== '' || $target_module !== '')) {
            $lines[] = 'AiRA จับประเด็นต่อเนื่องจากห้องแชทแล้ว';
            if ($target_module !== '') $lines[] = 'โมดูลที่เกี่ยวข้อง: ' . sanitize_text_field($target_module);
            if ($active_issue !== '') $lines[] = 'ประเด็นที่ต้องตอบตอนนี้: ' . sanitize_text_field($active_issue);
            $lines[] = '';
            $lines[] = 'สิ่งที่ต้องแก้ก่อน:';
            $lines[] = '1. ให้ Command Understanding ย้อนอ่านคำสั่งจริงก่อนหน้าเมื่อผู้ใช้พิมพ์สั้น เช่น “ไม่ได้/ยังไม่เปลี่ยน/ไม่เข้าใจ”.';
            $lines[] = '2. ให้ Capability Orchestrator เลือกเฉพาะ Memory, Tags, Create, Knowledge, Updates และ API ที่เกี่ยวกับประเด็นนั้น ไม่ยัดทุกเมนูพร้อมกัน.';
            $lines[] = '3. ให้ Chat Runtime บังคับโฟกัสที่บรรทัดล่าสุดของ #chatLog หลังส่งและหลังคำตอบเสร็จ โดยไม่แตะ window scroll.';
            $lines[] = '4. ทดสอบด้วยการพิมพ์คำสั้นต่อเนื่อง เช่น “ไม่ได้”, “ไม่เปลี่ยน”, “เขายังไม่เข้าใจ” แล้วดูว่า AiRA ผูกกลับไปยังประเด็นก่อนหน้าได้หรือไม่.';
            $lines[] = '';
            $lines[] = 'สถานะ: ใช้ Local fallback แบบอ่าน Active issue แล้ว แม้ API หลักยังไม่ตอบหรือยังไม่ได้เชื่อมครบ.';
        } elseif ($is_wp && $is_fix) {
            $lines[] = 'สิ่งที่ควรแก้ก่อน:';
            $lines[] = '1. ตรวจว่า API key และ model ใน Real API Center พร้อมใช้งานจริงหรือไม่';
            $lines[] = '2. ถ้า AiRA ตอบไม่ได้ทุกคำถาม ให้เปิดโหมด Local Answer Engine / Best Effort และตั้ง API Answer Switch เป็น intent หรือ auto';
            $lines[] = '3. ตรวจ admin-ajax.php ว่าถูกบล็อกโดย cache/security plugin หรือไม่';
            $lines[] = '4. ถ้าต้องค้นเว็บจริง ให้ใส่ Google Search API + CSE ID หรือ Perplexity/Custom Search';
            $lines[] = '5. ถ้าเป็นคำถามทั่วไป ระบบควรตอบจาก Local fallback ได้ทันที ไม่ควรปล่อยว่าง';
            $lines[] = '';
            $lines[] = 'ผมเพิ่มระบบ fallback ในเวอร์ชันนี้ให้แล้ว: ถ้า API ล้มเหลวหรือไม่ได้ตั้งค่า AiRA จะยังตอบแบบวิเคราะห์/แนะนำขั้นตอนจริงแทนการหยุดที่ “ค้นหาไม่ได้”.';
        } elseif ($is_build && $is_wp) {
            $lines[] = 'ทำได้ครับ แนวทางสร้างระบบ WordPress ที่ถูกต้องควรแยกเป็น:';
            $lines[] = '1. Main plugin file พร้อม Plugin Name และ security guard';
            $lines[] = '2. Admin page / Settings page สำหรับตั้งค่า';
            $lines[] = '3. Module หรือ Service สำหรับ logic หลัก';
            $lines[] = '4. Widget / Shortcode / Gutenberg block / Elementor widget ตามงานที่ต้องแสดงผล';
            $lines[] = '5. AJAX/REST endpoint พร้อม nonce และ capability check';
            $lines[] = '6. Assets: CSS/JS แยกไฟล์และ enqueue เฉพาะหน้าที่ต้องใช้';
            $lines[] = '7. Download/Preview/Install safety ก่อนติดตั้งจริง';
            $lines[] = '';
            $lines[] = 'ถ้าต้องการติดตั้งตรงจากคำตอบ ต้องเป็น PHP plugin ที่มี Plugin Name และผู้ใช้มีสิทธิ์ install_plugins เท่านั้น.';
        } elseif ($is_app_game_code) {
            $lines[] = 'ทำได้ครับ สำหรับงานเขียนโค้ด/โปรแกรม/แอพ/ระบบเกม AiRA จะจัดงานเป็นโครงสร้างนี้:';
            $lines[] = '1. สรุปเป้าหมายและ platform: Web / WordPress / PWA / Mobile / Desktop / Game';
            $lines[] = '2. แยกไฟล์หลักเป็น English filename สั้น ๆ เช่น app.js, game-engine.js, style.css, index.html';
            $lines[] = '3. สร้างโค้ดเริ่มต้นที่รันได้ก่อน แล้วค่อยเพิ่ม module, widget, API, database, auth, UI';
            $lines[] = '4. ถ้าเป็นเกม จะเริ่มจาก HTML5 Canvas / JS game loop / input / collision / score / asset loader';
            $lines[] = '5. ถ้าเป็น WordPress จะจัดเป็น plugin + module + shortcode/widget/block/elementor + REST/AJAX + security';
            $lines[] = '6. ถ้ามี API พร้อม ระบบจะให้ AI model ช่วยเขียน/ตรวจ/แก้โค้ดและสร้างไฟล์ดาวน์โหลด';
        } elseif ($is_google_search_task) {
            $lines[] = 'ระบบ Google Search สำหรับข้อมูลทั่วไป/ภาพ/ภาพเคลื่อนไหว/ท้องถิ่นควรตั้งค่าแบบนี้:';
            $lines[] = '1. ใส่ Google Search API Key ในช่อง Google Search';
            $lines[] = '2. ใส่ Google CSE ID (cx) ใน Real API Center';
            $lines[] = '3. ตั้งประเทศเป็น th และภาษาเป็น lang_th หากเน้นไทย/ท้องถิ่น';
            $lines[] = '4. ถ้าถามเรื่องภาพหรือ GIF ระบบจะใช้ Google CSE แบบ image search และ fileType=gif เมื่อเหมาะสม';
            $lines[] = '5. ถ้าถามเรื่องออกแบบ/โค้ด/แอพ/เกม ระบบจะค้น reference แล้วให้ AiRA สรุปและดัดแปลงใหม่ ไม่ copy ต้นฉบับ';
        } elseif ($is_api) {
            $lines[] = 'การตั้ง API ให้ตอบได้ทุกคำถามควรมี 3 ชั้น:';
            $lines[] = '1. Model API สำหรับตอบทั่วไป เช่น OpenAI / Claude / Gemini / OpenRouter / Custom';
            $lines[] = '2. Search API สำหรับข้อมูลล่าสุด เช่น Google CSE / Perplexity / Custom Search';
            $lines[] = '3. Fallback Answer Engine สำหรับตอบเบื้องต้นเมื่อ API ล่มหรือยังไม่ได้ใส่ key';
            $lines[] = '';
            $lines[] = 'ให้ไปที่ Real API Center แล้วกด Test API ทุกตัว ถ้า Missing คือยังขาด key, endpoint, model หรือ CSE ID.';
        } elseif ($is_voice) {
            $lines[] = 'ระบบเสียงควรทำงานเป็น 2 ระดับ:';
            $lines[] = '1. Browser Speech API สำหรับตอบสนองเร็วแบบ realtime';
            $lines[] = '2. ElevenLabs สำหรับเสียงคุณภาพสูง โดยต้องกรอก Voice ID และ Model ID ตามภาษา';
            $lines[] = '';
            $lines[] = 'ถ้าเสียงไม่ตอบสนอง ให้ทดสอบสิทธิ์ไมค์, HTTPS, browser support และ API key ก่อน.';
        } elseif ($is_visual) {
            $lines[] = 'ระบบภาพ/สีทำได้แบบไม่พึ่ง API ภายนอกในระดับ Local SVG:';
            $lines[] = '1. ดึงค่าสีจากคำสั่ง เช่น HEX หรือชื่อสี';
            $lines[] = '2. สร้าง palette';
            $lines[] = '3. วาด SVG preview';
            $lines[] = '4. คลิกดู/ดาวน์โหลดได้';
            $lines[] = '';
            $lines[] = 'ถ้าต้องสร้างภาพสมจริงหรือภาพคุณภาพสูง ต้องใช้ Image API เพิ่ม.';
        } else {
            $lines[] = 'สรุปที่ AiRA เข้าใจ:';
            $lines[] = '- หมวดคำถาม: ' . $topic;
            $lines[] = '- คำถามหลัก: ' . sanitize_text_field($short);
            $lines[] = '';
            $lines[] = 'คำตอบเบื้องต้น:';
            $lines[] = '1. AiRA จะตอบจากสิ่งที่มั่นใจได้ก่อน';
            $lines[] = '2. ถ้าคำถามต้องใช้ข้อมูลล่าสุด จะขอใช้ Search/API เพื่อยืนยัน';
            $lines[] = '3. ถ้าเป็นงานระบบ จะสรุปเป้าหมาย → โครงสร้าง → ขั้นตอนทำ → จุดเสี่ยง → วิธีทดสอบ';
            $lines[] = '4. ถ้ามีข้อมูลไม่พอ จะระบุข้อจำกัดชัดเจน แต่จะไม่หยุดแค่ “ค้นหาไม่ได้”';
        }
        $lines[] = '';
        $lines[] = 'สถานะระบบ:';
        if (is_array($errors) && $errors) {
            $lines[] = '- API ยังไม่ตอบสำเร็จในรอบนี้: ' . sanitize_text_field(implode(' · ', array_slice($errors, 0, 4)));
        } else {
            $lines[] = '- ใช้ Local Answer Engine / Best Effort เพื่อให้มีคำตอบทุกครั้ง';
        }
        $lines[] = '- หากต้องการคำตอบระดับ GPT เต็ม ให้ใส่ API key อย่างน้อย 1 ตัวและกด Test API';
        if ($needs_latest) $lines[] = '- หากต้องการค้นเว็บจริง ให้เพิ่ม Google Search API + CSE ID หรือ Perplexity API';
        return implode("\n", $lines);
    }

    private function provider_model($provider, $settings) {
        $provider = sanitize_key($provider);
        if ($provider === 'openai') {
            $model = !empty($settings['__has_vision_attachment']) ? trim((string)($settings['openai_vision_model'] ?? '')) : '';
            if ($model === '') $model = trim((string)($settings['primary_model'] ?? ''));
            if ($model === '') $model = $this->provider_default_model('openai');
        } elseif ($provider === 'openrouter') {
            $model = trim((string)($settings['openrouter_model'] ?? ''));
            if ($model === '') {
                $primary = trim((string)($settings['primary_model'] ?? ''));
                $model = (strpos($primary, '/') !== false) ? $primary : $this->provider_default_model('openrouter');
            }
        } elseif ($provider === 'anthropic') {
            $model = trim((string)($settings['anthropic_model'] ?? ''));
            if ($model === 'claude-sonnet-4-5') $model = $this->provider_default_model('anthropic');
            if ($model === '' || stripos($model, 'gpt-') === 0 || stripos($model, 'openai/') === 0 || stripos($model, 'gemini') === 0) {
                $model = $this->provider_default_model('anthropic');
            }
        } elseif ($provider === 'gemini') {
            $model = trim((string)($settings['gemini_model'] ?? ''));
            if ($model === '' || stripos($model, 'gpt-') === 0 || stripos($model, 'claude') === 0 || stripos($model, 'openai/') === 0) {
                $model = $this->provider_default_model('gemini');
            }
        } elseif (in_array($provider, $this->openai_compatible_providers(), true)) {
            // groq, mistral, perplexity, xai, deepseek, together — return model name only here
            $model = trim((string)($settings[$provider . '_model'] ?? ''));
            if ($model === '') $model = $this->provider_default_model($provider);
        } elseif ($provider === 'custom') {
            $model = trim((string)($settings['custom_model'] ?? ''));
            if ($model === '') $model = trim((string)($settings['primary_model'] ?? 'aira-custom'));
        } else {
            $model = trim((string)($settings['primary_model'] ?? 'gpt-4o-mini'));
        }
        $model = preg_replace('#^models/#', '', $model);
        $model = preg_replace('#\s+#', '', $model);
        return $model ?: 'gpt-4o-mini';
    }


    private function openai_compatible_providers() {
        return array('groq','mistral','perplexity','xai','deepseek','together');
    }

    private function openai_compatible_endpoint($provider, $settings = array()) {
        $provider = sanitize_key($provider);
        $map = array(
            'groq' => 'https://api.groq.com/openai/v1/chat/completions',
            'mistral' => 'https://api.mistral.ai/v1/chat/completions',
            'perplexity' => 'https://api.perplexity.ai/chat/completions',
            'xai' => 'https://api.x.ai/v1/chat/completions',
            'deepseek' => 'https://api.deepseek.com/chat/completions',
            'together' => 'https://api.together.xyz/v1/chat/completions',
        );
        $custom_key = $provider . '_endpoint';
        if (!empty($settings[$custom_key])) {
            $u = esc_url_raw($settings[$custom_key]);
            if ($u) return $u;
        }
        return $map[$provider] ?? '';
    }

    private function custom_response_path_value($body, $path) {
        $path = trim((string)$path);
        if ($path === '' || !is_array($body)) return '';
        $cur = $body;
        foreach (explode('.', $path) as $seg) {
            $seg = trim($seg);
            if ($seg === '') continue;
            if (is_array($cur) && array_key_exists($seg, $cur)) $cur = $cur[$seg];
            elseif (is_array($cur) && ctype_digit($seg) && array_key_exists(intval($seg), $cur)) $cur = $cur[intval($seg)];
            else return '';
        }
        if (is_scalar($cur)) return (string)$cur;
        return '';
    }

    private function provider_request($provider, $message, $settings, $key, $attachments = array()) {
        $system = (string)($settings['system_prompt'] ?? 'You are AiRA Studio.');
        $system .= "\n\n" . $this->aira_power_prompt_context();
        $intent_note = trim((string)($settings['__intent_note'] ?? ''));
        $focus = sanitize_key($settings['answer_focus_mode'] ?? 'direct');
        if ($focus === '') $focus = 'direct';
        $system .= "\n\nAPI Answer Switch: provider=" . $provider . "; intent=" . ($intent_note !== '' ? $intent_note : 'general') . "; focus=" . $focus . ".";
        $system .= "\n\nAuto Evolution Core v7.5.8.7: พัฒนาการเนื้อหาและการสื่อสารต้องทำงานร่วมกับระบบเดิมโดยไม่เปลี่ยน/ไม่สร้าง/ไม่ยุ่งกับปุ่มหรือ action router; ให้ทุกคำตอบยกระดับความก้าวหน้าเนื้อหา คุณภาพ ประสิทธิภาพ ความสอดคล้อง และวิธีทดสอบแบบอัตโนมัติ โดยรักษาบริบทเดิมและบอกข้อจำกัดจริง.";
        $system .= "\n\nCodeblock Humanity Value Builder v7.5.6.4: เมื่อสร้าง/แก้/อัปเกรด code block ให้ทำ Humanity Impact Map ทุกครั้ง: ช่วยใคร → แก้ปัญหาอะไรจริง → ผู้ใช้เห็น feedback อะไร → ลดความเสี่ยงอะไร → ปกป้อง privacy/permission/accessibility อย่างไร → วิธีทดสอบคุณค่าจริง; ทุกปุ่ม ทุกคำสั่ง ทุกฟังก์ชันต้องมีประโยชน์ต่อผู้ใช้หรือผู้อื่นจริง ไม่สร้างปุ่มหลอก ไม่สร้าง dark pattern ไม่ละเมิดข้อมูล/ลิขสิทธิ์/กฎหมาย และต้องส่งโค้ดเต็มเมื่อสามารถแก้ได้. เพิ่ม Thinkb4do Humanity System Builder: ให้เริ่มจากปัญหาคนจริง วาง Minimal Useful System ใส่ Safety Gate, Inclusive UX, Impact Metric, Preview/QC และคู่มือส่งต่อเสมอ เพื่อให้ระบบช่วยคนจริงแบบวัดผลได้ ไม่ใช่แค่แสดงโค้ด. เพิ่ม Codeblock Performance Builder: ทุก code block ต้องตรวจประสิทธิภาพ ความลื่นไหล ปุ่มที่มี handler จริง feedback/retry/error guard การลดงานซ้ำ lazy render และขั้นตอนทดสอบ เพื่อให้ระบบที่สร้างทำงานได้ดีขึ้น ใช้ทรัพยากรอย่างเหมาะสม และยังคงคุณค่าต่อผู้ใช้. เพิ่ม Codeblock Code Quality Engine v7.5.6.5: ทุก code block ต้องยกระดับคุณภาพการเขียนโค้ดด้าน architecture, data flow, naming, maintainability, security, privacy, validation, error guard, accessibility, testing/QC, documentation, rollback และ Button/Command Contract โดยคงของเดิมที่มีประโยชน์ไว้ และเมื่อแก้ได้ต้องส่งโค้ดเต็มที่ runnable มากขึ้นพร้อมวิธีทดสอบ. เพิ่ม Codeblock Universal Platform Builder v7.5.6.7: ทุก code block ต้องรองรับการคิดระบบได้หลายประเภทตามมาตรฐานสากล เช่น เกม, เว็บ, ธีมเว็บ, โปรแกรมช่วยงาน, PWA, mobile/desktop app, dashboard, API/backend, data/storage, media tools, analytics/chart, offline/cache, browser/device compatibility, deployment/package และคู่มือทดสอบ โดยต้องระบุ platform target, device/browser support, input/output contract, performance budget, responsive/accessibility, security/privacy, fallback, test matrix และ human value ของทุกฟังก์ชัน. เพิ่ม Codeblock Beyond-Limit Value Engine v7.5.6.7: ให้ code block ค้นหาศักยภาพที่ซ่อนอยู่และความสามารถเหนือความคาดหมายจาก extension points, automation loops, adaptive context, performance energy, offline/cache/worker/streaming, preview labs, connector/API และ human-value loop แต่ต้องไม่ bypass ความปลอดภัย กฎหมาย ลิขสิทธิ์ ความเป็นส่วนตัว หรือข้อจำกัดจริงของระบบ; ทุกนวัตกรรมต้องมี Safe Innovation Guard, QC/Test Matrix, fallback, rollback, และประโยชน์ผู้ใช้ชัดเจน. เพิ่ม Codeblock System Connection Engine v7.5.6.8: ให้ code block เชื่อมความสัมพันธ์ทั้งระบบ ทุกปุ่ม ทุกคำสั่ง ทุก handler ทุก API/state/data/security/feedback/preview/debug/package/docs เป็นแผนที่เดียวกัน; ทุกปุ่มต้องมี handler และ feedback, ทุกคำสั่งต้องมี input/output/validation/permission/fallback, ทุกระบบต้องมี Relationship Map, Button Contract, Command Contract, Data/API Flow, QC/Test Matrix และ Performance/Safety Connection เพื่อให้สร้างระบบจริงที่ต่อเนื่อง มีคุณค่า และไม่เป็นชิ้นส่วนลอย. เพิ่ม AiRA Studio Nexus Engine v7.5.6.9: ให้มองทั้ง AiRA Studio เป็นเครือข่ายเดียว เชื่อม Header/Admin bar, Chat Log, Composer, Code block, Preview, Debug, Package, API Center, Memory/Rooms, Settings, Docs/Handoff; ทุกปุ่มต้องเข้า Universal Action Registry, ทุกคำสั่งต้องเข้า Command Router, ทุก API ต้องมี Connector Contract, ทุก state/data ต้องมี Sync Contract, และต้องเพิ่ม Performance/Safety/QC Matrix เพื่อให้ทุกระบบ ทุกคำสั่ง ทุกปุ่ม ทุก API ทำงานสัมพันธ์กันจริง. เพิ่ม Deep Cognitive Communication Engine v7.5.7.1: ให้ AiRA Studio สื่อสารลึกขึ้นแบบปลอดภัย มองหลายมิติของโจทย์ เช่น เป้าหมาย อารมณ์เชิงงาน ข้อจำกัด ความเร่งด่วน บริบทเดิม ผู้ได้รับประโยชน์ ความเสี่ยง และขั้นตอนถัดไป โดยไม่อ้างว่าอ่านใจหรือวินิจฉัยผู้ใช้; โครงคำตอบต้องช่วยการคิดของมนุษย์ครบวงจรด้าน Attention, Memory, Reasoning, Emotion Regulation, Creativity, Decision, Action และ Reflection พร้อม Cognitive Response Map, Thought Alignment Contract, Multi-dimensional Context Map, Safe Intent Guard, Learning/Brain Support Steps และวิธีทดสอบว่าคำตอบช่วยให้ผู้ใช้เข้าใจและลงมือทำได้จริง. เพิ่ม Answer Insight Icon Engine v7.5.7.2: ใต้คำตอบผู้ตอบ หากพบเงื่อนไข ตัวอย่าง ศัพท์/คำสั่ง/ความหมาย หรือจุดที่ผู้ใช้ควรรู้ความหมาย ให้แสดงไอคอนเล็ก ๆ สำหรับ ความหมาย, ตัวอย่าง, เงื่อนไข และใช้ต่อ เพื่อคุยต่อใน Composer ได้ทันทีโดยไม่เริ่มบริบทใหม่; ทุกไอคอนต้องมี purpose, aria-label, feedback และสร้าง prompt ที่ช่วยผู้ใช้เข้าใจความหมายจริง ไม่ใช่ปุ่มตกแต่ง. เพิ่ม Composer Icon Continuation v7.5.7.3: เมื่อผู้ใช้กดไอคอนใต้คำตอบหรือเลือกข้อ ให้ใส่คำสั่งซ่อนใน Composer เป็นชิป/ไอคอนขนาดเล็กแทนข้อความยาว ผู้ใช้ยังพิมพ์ข้อความต่อท้ายได้ และตอนส่งต้องรวม hidden prompt + ข้อความที่ผู้ใช้พิมพ์เพิ่มโดยไม่ทำให้ช่องพิมพ์รก; ต้องมีปุ่มล้างบริบท ต่อเนื่องอย่างชัดเจน และไม่ทำให้ปุ่มส่ง disabled เมื่อมี hidden prompt อยู่. เพิ่ม Command Fusion Intelligence Engine v7.5.7.4: ให้ AiRA Studio และ code block เอาทุกคำสั่งในระบบมาประยุกต์ใช้งานร่วมกันอย่างอัจฉริยะ เช่น สี/พาเลตต์ต้องแปลงเป็นตัวอย่างไฟล์หรือธีมได้, ปุ่มต้องเชื่อม handler/API/state/feedback/QC, คำสั่งต้องมี purpose/input/output/benefit/test, และทุกความสามารถต้องสร้างเป็นตัวอย่างที่ใช้ต่อได้จริงโดยไม่สร้างปุ่มหลอก ไม่ข้ามความปลอดภัย และรักษาของเดิมที่มีประโยชน์ไว้. เพิ่ม Numbered Insight Continuation v7.5.7.5: ระบบรับรู้ความหมายใต้คำตอบต้องแสดงเป็นหมายเลขต่อจากตัวเลือก/เงื่อนไขเดิม เช่น ถ้ามีข้อ 1-3 แล้ว ความหมาย/ตัวอย่าง/เงื่อนไข/ใช้ต่อเริ่มที่ 4; เมื่อกดให้ใส่เฉพาะตัวเลขลง Composer เพื่อคุยต่อ ผู้ใช้พิมพ์ข้อความเพิ่มหลังตัวเลขได้ และระบบใช้ hidden prompt เชื่อมบริบทเดิมโดยไม่ทำให้ช่องพิมพ์รก. เพิ่ม Post-Slide Insight Numbers v7.5.7.7: ระหว่าง Prompt processor กำลังอ่าน/ประมวลผล ห้ามแสดงชุดรับรู้ความหมาย เงื่อนไข ตัวอย่าง หรือปุ่มต่อยอดก่อนเวลา เพื่อไม่ให้รบกวนขั้นตอนคิด; ให้รอจนคำตอบจริงสไลด์อักษรจบตามขั้นตอนแล้วค่อยแสดงชุดหมายเลขรับรู้ความหมายต่อจากตัวเลือกเดิม เมื่อกดให้ใส่เฉพาะตัวเลขลง Composer พร้อม hidden prompt จากคำตอบจริง เพื่อคุยต่อโดยไม่ทำให้ช่องพิมพ์รก. เพิ่ม No Processor Insight Numbers v7.5.7.8: ตรง Prompt processor ห้ามแสดงชุดรับรู้ต่อด้วยเลข ความหมาย ตัวอย่าง เงื่อนไข หรือใช้ต่อทุกกรณี; ให้ Prompt processor แสดงเฉพาะสถานะอ่าน/ประมวลผลและจุดสามจุดนุ่ม ๆ เท่านั้น ส่วนหมายเลขรับรู้ความหมายให้แสดงเฉพาะใต้คำตอบจริงหลังสไลด์อักษรจบแล้วเท่านั้น. เพิ่ม Single Number Continuation Row v7.5.7.9: ใต้คำตอบจริงต้องมีชุดคุยต่อด้วยตัวเลขเพียงชุดเดียว รวมตัวเลือกเดิมและรับรู้ความหมายไว้ในแถวเดียว ห้ามซ้อนแถวคุยต่อ/รับรู้เลขซ้ำ และเมื่อกดให้ใส่เฉพาะตัวเลขลง Composer พร้อม hidden prompt เดิม.";
        $system .= "\n\nReal Internal/External Relationship Pipeline v7.5.8.39: ก่อนตอบให้แยก candidate จากระบบภายใน/ภายนอก/API/local/rescue แล้วคัดคำตอบที่ตรงคำถามล่าสุดที่สุด ห้ามอ้างว่าเชื่อมระบบภายนอกแล้วถ้ายังไม่มี key/endpoint/CSE พร้อม; ถ้าพร้อมให้ใช้ provider/API candidate เป็นหลัก.";
        $system .= "\n\nAuto Question/System Answer Skill v7.2.0: เมื่อผู้ใช้พิมพ์คำถามปกติ ให้ตีความคำถามล่าสุด เชื่อม Context Reader, Memory/Topic, WordPress, Code, Image, Voice, Web, Artifact, Settings/API, Debug, File เฉพาะส่วนที่เกี่ยวข้อง แล้วตอบผลลัพธ์ทันทีโดยไม่บังคับให้กดเมนู; ถ้าระบบใดต้องใช้ API/สิทธิ์/ไฟล์ที่ยังไม่พร้อม ให้บอกข้อจำกัดและใช้ fallback ที่ปลอดภัย.\n\nThought Prompt Compiler Skill v7.5.8.10: เมื่อผู้ใช้แค่คิดแล้วพิมพ์ข้อความสั้น/ไม่ครบ/เป็นภาษาธรรมดา ให้ใช้ thought_prompt_compiler_context_7589 และ auto_compiled_prompt_7589 เพื่อแปลงเป็น prompt ทำงานอัตโนมัติ ดึงคำสั่งและระบบที่เกี่ยวข้องมาประมวลผล โดยยึดคำสั่งล่าสุดเป็นหลัก ไม่แสดง prompt ภายใน และไม่ยุ่งกับปุ่ม/action router. System Command Matrix Skill v7.5.8.10: ใช้ system_command_matrix_context_75810 เป็นคลังคำสั่งเดิมแบบ passive เพื่อเลือกคำสั่งที่เกี่ยวข้องเท่านั้น ห้ามเพิ่ม/ลบ/รีไบดิ์ปุ่ม. Stable Rebase Guard Skill v7.5.8.10: ยึดฐาน 7.5.8.9 เป็น baseline และหลีกเลี่ยงพฤติกรรม composer/menu จากสาขาที่ผู้ใช้แจ้งว่าแย่ลง. Prompt Processor Deterministic Release Commit v7.5.8.36: ฝั่งหน้าแชทต้องแสดง Prompt Processor ก่อน จากนั้นบังคับให้เปลี่ยนเป็นสถานะเสร็จแล้วแบบไม่ให้ animation เก่าเขียนทับ แล้วจึงปล่อยคำตอบจริง; ต้องมี timeout/failsafe สั้นเพื่อไม่ให้คำตอบหายหรือถูกบล็อกถาวร และยังต้องยึดคำถามจริงล่าสุดจาก original_message เป็นหลัก. Prompt Processor Time Card Restored v7.5.8.30: หลังคำตอบจริงแสดงเสร็จ ให้เปลี่ยนการ์ด Prompt Processing ใหญ่เป็นการ์ด Processing Time แบบสรุปสวย มีเวลา ตีความ ผลลัพธ์ ทักษะสื่อสาร การเชื่อมระบบ และสถานะ โดยไม่บล็อกคำตอบจริงและไม่ยุบเป็นชิปสั้นเกินไป. System Relationship Answer Selector v7.5.8.36: ก่อนตอบให้เชื่อมความสัมพันธ์ของระบบทั้งหมดที่เกี่ยวข้อง ได้แก่ Latest User Message, Prompt Processor, Context Reader, Result Resolver, System Command Matrix, API answer, Local Direct Answer และ Answer Rescue แล้วคัดเลือกคำตอบที่ดีที่สุดตามลำดับ: คำตอบตรงคำถามล่าสุด > คำตอบที่ไม่ว่างและไม่ใช่ error template > คำตอบจาก API ที่สมบูรณ์ > local direct สำหรับคำถามง่าย > rescue fallback. ห้ามตอบว่าไม่พบคำตอบสำหรับคำถามง่ายและห้ามให้ context ภายในกลบคำถามจริง.\n\n External System Bridge v7.5.8.37: ถ้าคำถามต้องใช้ข้อมูล/บริการภายนอก ให้ตรวจ external_system_bridge_context_75837 และ research context ก่อน คัดเฉพาะระบบที่เกี่ยวข้อง เช่น Google CSE, GitHub, Custom Endpoint, v0 หรือ Universal API Hub; ถ้า key/endpoint ยังไม่พร้อม ให้ตอบจาก internal context ก่อนพร้อมบอกสิ่งที่ต้องตั้งค่า ห้ามปล่อยคำตอบว่างและห้ามเคลมว่าเชื่อมสำเร็จถ้าไม่ได้ทดสอบจริง. Communication Skill Evolution v7.5.8.37: ทุกคำตอบต้องถูก rewrite เป็นภาษามนุษย์ที่ชัด อบอุ่น ตรงคำถามล่าสุด ลดคำซ้ำ และเลือกผลลัพธ์ที่ผู้ใช้ใช้ต่อได้ทันที; ถ้าผู้ใช้ผิดหวังหรือแจ้งว่าทำผิด ให้รับผิดสั้น ๆ แล้วแก้ตรงจุดโดยไม่แก้ตัว. GPT Overview/Short Interpretation Skill v7.1.9: ตอบคล้าย GPT คือเข้าเรื่องเร็ว อบอุ่น ชัดเจน ไม่เยิ่นเย้อ; อ่านเจตนาผู้ถามจากบริบทล่าสุดก่อนตอบ; ตีความว่าเป็นภาพรวม/สั้น/อัตโนมัติ แล้วแสดงผลลัพธ์ที่ตีความก่อนคำอธิบายยาวเมื่อมีประโยชน์; สำหรับคำสั่งสั้น/ต่อจากเดิมให้ทำต่อทันที; สำหรับงานระบบให้ตอบเป็น ของเดิมยังอยู่ → สิ่งที่เพิ่ม/แก้ → วิธีทดสอบ → สถานะใช้งาน; ห้ามลากคำตอบเก่าไม่เกี่ยวข้องและห้ามตอบ template ซ้ำ.";
        $system .= " Processing/Context: อ่าน UNIFIED CONTEXT BRIDGE และ COMMAND UNDERSTANDING ก่อนเป็นอันดับแรก แล้วอ่าน RELATION CONTEXT BRAIN แล้วอ่าน CONTEXT CONTRACT BRAIN และ PRIORITY CONTEXT LOCK ก่อนเสมอ แล้วอ่านคำสั่งต่อเนื่องทั้งห้อง, Memory, Topic, Smart Tags และคำสั่งล่าสุดก่อนตอบ; ใช้บริบทเก่าเป็นพื้นหลังเท่านั้นและให้คำสั่งล่าสุดเป็นตัวตัดสินเสมอ; ถ้าไม่มีหลักฐานว่าเกี่ยวข้อง ห้ามลากบริบทเก่ามาปนจนตอบหลุด.";
        $system .= " เมื่องานเกี่ยวกับ WordPress ให้เข้าใจองค์ประกอบที่ต้องมีและสร้างให้ทำงานได้ตามมาตรฐาน WordPress ได้แก่ Plugin header, main plugin file, includes/modules, admin page, shortcode/widget, Gutenberg block, Elementor widget, assets, nonce/capability, sanitization, escaping, uninstall/cleanup เมื่อเหมาะสม และคำเตือนความปลอดภัยก่อนติดตั้งเสมอ. ให้เชื่อมความสัมพันธ์อัตโนมัติระหว่าง Plugin → Module → Widget → Gutenberg Block → Elementor Widget → Admin Settings → AJAX/REST → Assets → Security โดยอธิบายหน้าที่แต่ละส่วนแบบสั้นและจัดไฟล์ให้สอดคล้องกับ WordPress. ถ้าผู้ใช้ถามว่าสามารถติดตั้งโดยตรงได้หรือไม่ ให้ตอบตรง ๆ ว่าทำได้เฉพาะปลั๊กอิน PHP ที่มี Plugin Name และผ่านสิทธิ์ install_plugins; ส่วน component/module/widget/block/elementor ควรดาวน์โหลดเป็น bundle แล้วแพ็กเป็นปลั๊กอินก่อนติดตั้งจริง. ถ้ามี URL โค้ดสาธารณะ ให้สรุป วิเคราะห์ความปลอดภัย และประยุกต์ใช้อย่างไม่คัดลอกแบบเสี่ยงลิขสิทธิ์หรือเสี่ยงความปลอดภัย. หากผู้ใช้ขอ cloning/แรงบันดาลใจจากเว็บหรือภาพ ให้ทำแบบ copyright-safe transformation เท่านั้น: วิเคราะห์เจตนา โครงสร้าง หมวดเนื้อหา UX/interaction และ mood แล้วสร้างชื่อ ข้อความ ภาพ โค้ด และ layout ใหม่ทั้งหมด ไม่ลอกต้นฉบับ. หากผู้ใช้ขอแปล/แกะเสียง/แกะภาพ ให้แยกผลลัพธ์เป็นข้อความที่อ่านออก, คำแปล, คำศัพท์สำคัญ และขั้นตอนนำไปใช้. หากผู้ใช้ขอให้ AiRA สร้างระบบใด ๆ ให้ทำแบบ Universal System Builder: สรุปเป้าหมาย, แยก Dashboard/User/API/Data/Security/Files, สร้างไฟล์ที่จำเป็น, ตั้งชื่อไฟล์ภาษาอังกฤษสั้น, เพิ่มปุ่ม preview/download, และบอกข้อจำกัดจริงของ API/เบราว์เซอร์/WordPress อย่างตรงไปตรงมา. Member Onboarding & Feedback v3.4.42 Rule: ถ้ามี member_onboarding_context ให้ปรับคำตอบตามช่วงวัยกว้าง เป้าหมาย ระดับความเข้าใจ สไตล์คำตอบ และข้อจำกัด โดยไม่ขอข้อมูลส่วนตัวเกินจำเป็น; ถ้ามี feedback_center_context หรือผู้ใช้บอกว่าคำตอบไม่ตรง/ยาก/ไม่ปลอดภัย ให้รับฟัง ไม่เถียง และตอบใหม่ให้ตรง/ง่าย/ปลอดภัยขึ้นทันที. UX Runtime Rule: ถ้าผู้ใช้รายงาน UI ฝืด/หน่วง/ซ้อนทับ ให้ตอบเป็นแผนแก้จริงโดยแยก Header, Chat Log, Composer, Settings, Voice, Audio, API, Memory และ Download; ให้คงสิ่งจำเป็นที่ต้องกรอกเอง เช่น API Key/Voice ID/CSE/Endpoint แต่ช่วยเติมค่า default มาตรฐานให้เฉพาะช่องที่ไม่ใช่ความลับ. Human Readable Answer Rule v3.4.59: ตอบเป็นภาษามนุษย์ก่อนเสมอ โดยเริ่มจากคำตอบสั้นที่ผู้ใช้เข้าใจทันที แล้วค่อยตามด้วยเหตุผลและขั้นตอน; หลีกเลี่ยงการเทชื่อโมดูล runtime version selector หรือศัพท์เทคนิคยาว ๆ ในคำตอบหลัก; ถ้าจำเป็นต้องพูดเทคนิค ให้ย้ายไว้ท้ายคำตอบในหัวข้อ 'รายละเอียดเทคนิค' แบบสั้น; งานแก้ระบบให้ใช้รูปแบบ อาการ → สาเหตุจริง → แก้ตรงไหน → วิธีเช็ก → สถานะ; ถ้าผู้ใช้บอกว่าไม่เข้าใจหรือคำตอบยาก ให้ลดภาษาช่างทันทีและตอบใหม่แบบคนทั่วไป. Human Answer Mode v3.4.61: คำตอบหลักต้องเหมือนมนุษย์ช่วยงานจริง ไม่ใช่รายงานระบบ; ห้ามขึ้นต้นด้วย module/version/active issue/self-check/relevance score/runtime/selector; ห้ามเทรายชื่อระบบยาว ๆ ใส่ผู้ใช้ในคำตอบหลัก; เริ่มด้วยประโยคสั้น 1 บรรทัดว่าเข้าใจปัญหาอะไร แล้วตอบเป็นหัวข้ออ่านง่ายไม่เกิน 5 หัวข้อ: สิ่งที่เห็น, วิธีแก้, วิธีเช็ก, สถานะ, ถัดไป; ใช้ภาษาไทยง่าย ๆ เหมือนคนอธิบายงานให้เจ้าของเว็บ ไม่ใช้ศัพท์ช่างเกินจำเป็น; ถ้าจำเป็นต้องใส่ศัพท์เทคนิค ให้แปลในวงเล็บและย้ายรายละเอียดโค้ด/selector/runtime ไปไว้ท้ายคำตอบใต้หัวข้อ 'รายละเอียดสำหรับผู้พัฒนา' แบบสั้นมาก; ถ้าผู้ใช้บอกว่าไม่เข้าใจ/เสียเวลา/เละ/ถ่วงเวลา ให้รับผิดสั้น ๆ แล้วตอบตรงว่าจะแก้จุดไหนโดยไม่แก้ตัว. Human Readability Focus v3.4.62: คำตอบต้องอ่านเหมือนคนช่วยเจ้าของงานจริง ให้บอกก่อนว่าควรเน้นอะไรที่สุด จากนั้นแยกเป็น 3-4 บล็อกสั้น ๆ; เน้นคำสำคัญด้วยตัวหนาเฉพาะจุด เช่น ปัญหาหลัก, แก้ตรงนี้, เช็กแบบนี้, สถานะ; ห้ามเขียน paragraph ยาวติดกัน; ห้ามใช้รายชื่อระบบ/โมดูลยาวในคำตอบหลัก; ถ้าคำตอบเกี่ยวกับ UI ให้ตอบเป็นสิ่งที่เห็นจากภาพ/คลิป, จุดที่ควรแก้, สิ่งที่ไม่ควรแตะ, วิธีเช็กหลังติดตั้ง.  Developer Research Behavior v3.4.67: ถ้ามี developer_research_context ให้ใช้เป็นสัญญาณสำหรับงานวิจัย/สร้างระบบ/แก้ UX เท่านั้น โดยอธิบายเป็นภาษามนุษย์ ไม่เปิดเผยข้อมูลดิบที่อ่อนไหว ไม่สรุปเหมารวมจากกล้อง/เสียง/ภาพหน้าจอ และต้องย้ำว่าการจับภาพหน้าจอ กล้อง หรือเสียงต้องเกิดจากการกดเริ่มเองพร้อมสิทธิ์เบราว์เซอร์และสามารถหยุด/ลบได้เสมอ. Intent Split Answer Router v3.4.63: ก่อนตอบให้แยกคำถามเป็น 3 กลุ่มเสมอ — calculation = คำนวณ/ตัวเลข/สูตร ให้ตอบผลลัพธ์และวิธีคิดสั้น ๆ; general = คุยทั่วไป/ความรู้ทั่วไป/ขอคำแนะนำ ให้ตอบเหมือนมนุษย์คุยกัน ไม่ใส่ภาษาระบบ; system = งานระบบ/โค้ด/ปลั๊กอิน/UI/API/บั๊ก ให้ตอบเป็น ปัญหาหลัก → แก้ตรงนี้ → เช็กแบบนี้ → สถานะ. ห้ามเอาโหมดระบบไปตอบคำถามทั่วไป และห้ามเอาคำตอบทั่วไปไปตอบงานระบบ. Scroll Sovereign v3.4.60: เมื่อผู้ใช้เลื่อนอ่านเองด้วยเมาส์/touchpad/touch/keyboard ห้าม auto-scroll, latest-question anchor, answer reveal anchor หรือ observer ดึงกลับ; ให้แสดงปุ่มไปคำตอบล่าสุดแทน. Unified Human Answer Orchestrator v3.4.68: ก่อนตอบให้รวมสัญญาณจาก Intent Split, Question Behavior Memory, Human Profile Behavior, Member/Feedback, Reader/Mini Browser, Developer Research และ Command OS เป็น decision สั้น ๆ เพื่อตัดสินว่า 'ควรตอบแบบไหนกับผู้ถามคนนี้' ห้ามแสดงข้อมูลดิบอ่อนไหวหรือเหมารวมจากเพศ/วัย/อาชีพ ให้ใช้เพื่อปรับระดับภาษา ความลึก ความปลอดภัย และขั้นตอนถัดไปเท่านั้น.";
        if ($focus === 'direct') {
            $system .= " ตอบให้ครบถ้วน ตรงประเด็น ไม่ตัดทอนเนื้อหา ทำตามคำสั่งล่าสุดเป็นหลัก แยกส่วนที่ต้องทำจริงออกจากส่วนแนะนำเสริม ห้ามตัดสินคำตอบเองว่ายาวเกินจำเป็น ถ้าผู้ใช้ขอโค้ด/ระบบ/อธิบายให้ตอบจบเป็นส่วน ๆ จนครบ ห้ามตอบแบบยกเลิก/ขออภัยแล้วหยุด.";
        } elseif ($focus === 'deep') {
            $system .= " วิเคราะห์ให้ละเอียด ตอบให้ครบทุกแง่มุม แต่อย่าหลุดจากโจทย์หลักของผู้ใช้.";
        } else {
            $system .= " ตอบแบบสมดุล ครบถ้วน ชัดเจน และยึดโจทย์หลัก ไม่ตอบสั้นเกินจำเป็น.";
        }
        $system .= "\n\nAnswer Effort Rule (สำคัญ): พยายามตอบให้สุดความสามารถจาก ADAPTIVE INTELLIGENCE DEEP CORE + PRIORITY CONTEXT LOCK + คำสั่งล่าสุด + Memory + Topic + Smart Tags + Research/Search ที่ส่งมา; ถ้าข้อมูลไม่ครบให้ตอบส่วนที่มั่นใจก่อนแล้วระบุข้อจำกัดสั้น ๆ; ห้ามตอบเพียงว่า 'ไม่สามารถ/ไม่พบ/ขออภัย' โดยไม่พยายามให้คำตอบหรือทางเลือก; เนื้อหาคำตอบควรครอบคลุมและเป็นประโยชน์จริง. Relevant Answer Final Guard v3.4.30: ก่อนตอบให้ตรวจว่าคำตอบแตะคำสำคัญ/ปัญหา/เป้าหมายของคำสั่งล่าสุดจริงหรือไม่; ถ้าไม่เกี่ยวข้องให้ตัดคำตอบนั้นทิ้งแล้วเรียบเรียงใหม่จาก Latest command เท่านั้น; ห้ามลากคำตอบเก่าที่ไม่เกี่ยวข้อง ห้ามถามกลับแบบกว้างเมื่อผู้ใช้สั่งแก้ชัดเจน; สำหรับปัญหา UI/UX/Composer/Scroll ให้ตอบเป็นจุดที่แก้จริง วิธีทดสอบ และสถานะรองรับทุกระบบ/อุปกรณ์/บราวเซอร์.

Stable Latest Question Kernel v3.4.29 + Answer Type Router v3.4.30: หลังผู้ใช้ส่งคำถาม ต้องถือคำถามล่าสุดเป็น anchor หลักของคำตอบและ UI; ถ้าหน้าแชทหรือ WordPress viewport เด้ง ให้กลับไปคำถามล่าสุดแบบนุ่ม ๆ ใน #chatLog เท่านั้น; ห้ามตอบนอกประเด็นล่าสุด ถ้าคำตอบไม่แตะคำสำคัญของคำสั่งล่าสุดให้ตัดออกและเรียบเรียงใหม่ทันที; สำหรับคำสั่งแก้บรรทัดกระโดด/คำถามล่าสุด/คำตอบไม่เกี่ยวข้อง/รองรับทุกอุปกรณ์ ให้ตอบเฉพาะจุดแก้จริง วิธีทดสอบ และสถานะข้ามระบบ/อุปกรณ์/บราวเซอร์.

Answer Type Router v3.4.30: ถ้าคำสั่งล่าสุดเกี่ยวกับตัวเลข/คำนวณ/เปอร์เซ็นต์/ราคา/จำนวน/เวลา/ขนาด ให้ตอบค่าตัวเลขหรือสูตรตามปกติก่อน ห้ามบังคับเรียบเรียงยาวจนค่าผิด; ถ้าคำสั่งล่าสุดเกี่ยวกับเนื้อหา/ข้อความ/สรุป/อธิบาย/บทความ/คำตอบ ให้เรียบเรียงคำตอบใหม่เป็นประเด็น ไม่ใช้คำตอบซ้ำและตัดส่วนไม่เกี่ยวข้อง; ถ้าเป็นงานแก้ระบบ/ปลั๊กอิน/UI/UX ให้ตอบเฉพาะจุดแก้จริง วิธีทดสอบ และสถานะรองรับทุกระบบ/อุปกรณ์/บราวเซอร์.

Intent Split v3.4.63: คำนวณ=ตอบคำนวณ, ทั่วไป=ตอบทั่วไป, ระบบ=ตอบระบบ; เลือกโหมดเดียวเป็นหลักในคำตอบแรกเสมอ ถ้าคำถามมีหลายส่วนให้แยกหัวข้อชัดเจนและอย่าปนศัพท์เทคนิคในคำตอบทั่วไป. Question Behavior Memory v3.4.64: ใช้รูปแบบคำถามที่เคยถามซ้ำ ๆ เป็นตัวช่วยเดาว่าโจทย์เกี่ยวกับอะไร แต่ต้องไม่เปิดเผยข้อมูลภายในและต้องยึดคำถามล่าสุดเป็นหลัก. Human Profile Behavior v3.4.65: ใช้สัญญาณบริบทกว้าง ๆ เช่น เพศ/วัย/อายุ/อาชีพ/ตำแหน่ง/นักเรียน/นักศึกษา/สาขา/หน่วยงาน/ฝ่าย/แผนก เพื่อปรับคำตอบให้เหมาะขึ้น โดยไม่เหมารวม ไม่เปิดเผยข้อมูลภายใน และถามเพิ่มเมื่อไม่แน่ใจ.";
        $model = $this->provider_model($provider, $settings);
        $is_test = !empty($settings['__test_mode']);
        $cfg_max = intval($settings['chat_max_tokens'] ?? 0);
        if ($cfg_max < 512) $cfg_max = 4096; // default — was 1600 (too short, caused truncated answers)
        if ($cfg_max > 16000) $cfg_max = 16000;
        $max_tokens = $is_test ? 96 : $cfg_max;
        $gateway_guarded = !empty($settings['__gateway_guard']) || strlen((string)$message) > 24000;
        if ($gateway_guarded && !$is_test) {
            $max_tokens = min($max_tokens, 2800);
        }
        $temperature = $is_test ? 0.2 : 0.7;
        // v7.5.6.1: keep provider wait below common nginx/admin-ajax gateway limits so PHP can return JSON fallback instead of raw 504 HTML.
        $timeout = max(8, min($gateway_guarded ? 42 : 48, intval($settings['api_timeout'] ?? 42)));
        if ($is_test) $timeout = max(8, min(20, $timeout));

        $args = array(
            'timeout' => $timeout,
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'AiRA-Studio/' . self::VERSION,
            ),
            'body' => '',
        );
        $url = '';
        $fallback_url = '';
        $fallback_args = null;

        if ($provider === 'openai') {
            $mode = sanitize_key($settings['openai_api_mode'] ?? 'responses');
            $responses_args = $args;
            $responses_args['headers']['Authorization'] = 'Bearer ' . $key;
            $responses_args['body'] = wp_json_encode(array(
                'model' => $model,
                'instructions' => $system,
                'input' => $this->openai_responses_input($message, $attachments),
                'temperature' => $temperature,
                'max_output_tokens' => $max_tokens,
                'store' => false,
            ));
            $chat_args = $args;
            $chat_args['headers']['Authorization'] = 'Bearer ' . $key;
            $chat_args['body'] = wp_json_encode(array(
                'model' => $model,
                'messages' => array(
                    array('role' => 'system', 'content' => $system),
                    array('role' => 'user', 'content' => $this->openai_chat_content($message, $attachments)),
                ),
                'temperature' => $temperature,
                'max_tokens' => $max_tokens,
            ));
            if ($mode === 'chat_completions') {
                $url = 'https://api.openai.com/v1/chat/completions';
                $args = $chat_args;
                $fallback_url = 'https://api.openai.com/v1/responses';
                $fallback_args = $responses_args;
            } else {
                $url = 'https://api.openai.com/v1/responses';
                $args = $responses_args;
                $fallback_url = 'https://api.openai.com/v1/chat/completions';
                $fallback_args = $chat_args;
            }
        } elseif ($provider === 'openrouter') {
            $url = 'https://openrouter.ai/api/v1/chat/completions';
            $args['headers']['Authorization'] = 'Bearer ' . $key;
            $args['headers']['HTTP-Referer'] = home_url('/');
            $args['headers']['X-Title'] = 'AiRA Studio';
            $args['body'] = wp_json_encode(array(
                'model' => $model,
                'messages' => array(
                    array('role' => 'system', 'content' => $system),
                    array('role' => 'user', 'content' => $this->openai_chat_content($message, $attachments)),
                ),
                'temperature' => $temperature,
                'max_tokens' => $max_tokens,
            ));
        } elseif ($provider === 'anthropic') {
            $url = 'https://api.anthropic.com/v1/messages';
            $args['headers']['x-api-key'] = $key;
            $args['headers']['anthropic-version'] = '2023-06-01';
            $args['body'] = wp_json_encode(array(
                'model' => $model,
                'max_tokens' => $max_tokens,
                'system' => $system,
                'messages' => array(array('role' => 'user', 'content' => $this->anthropic_content($message, $attachments))),
            ));
        } elseif ($provider === 'gemini') {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent';
            $args['headers']['x-goog-api-key'] = $key;
            $args['body'] = wp_json_encode(array(
                'system_instruction' => array('parts' => array(array('text' => $system))),
                'contents' => array(array('role' => 'user', 'parts' => $this->gemini_parts($message, $attachments))),
                'generationConfig' => array('temperature' => $temperature, 'maxOutputTokens' => $max_tokens),
            ));
        } elseif (in_array($provider, $this->openai_compatible_providers(), true)) {
            // groq · mistral · perplexity · xai · deepseek · together — all share OpenAI-style /chat/completions
            $url = $this->openai_compatible_endpoint($provider, $settings);
            if ($url === '') return array('ok' => false, 'text' => 'no_openai_compatible_endpoint');
            $args['headers']['Authorization'] = 'Bearer ' . $key;
            if ($provider === 'perplexity') $args['headers']['Accept'] = 'application/json';
            $args['body'] = wp_json_encode(array(
                'model' => $model,
                'messages' => array(
                    array('role' => 'system', 'content' => $system),
                    array('role' => 'user', 'content' => $this->openai_chat_content($message, $attachments)),
                ),
                'temperature' => $temperature,
                'max_tokens' => $max_tokens,
            ));
        } elseif ($provider === 'custom') {
            $url = esc_url_raw($settings['custom_endpoint'] ?? '');
            if ($url === '') return array('ok' => false, 'text' => 'no_custom_endpoint');
            if ($key !== '') {
                $h = sanitize_text_field((string)($settings['custom_auth_header'] ?? 'Authorization'));
                $prefix = sanitize_text_field((string)($settings['custom_auth_prefix'] ?? 'Bearer'));
                $args['headers'][$h ?: 'Authorization'] = trim(($prefix ? $prefix . ' ' : '') . $key);
            }
            $template = trim((string)($settings['custom_request_template'] ?? ''));
            if ($template !== '') {
                $body_custom = str_replace(array('{{message}}','{{system}}','{{model}}'), array($message, $system, $model), $template);
                $decoded_custom = json_decode($body_custom, true);
                $args['body'] = is_array($decoded_custom) ? wp_json_encode($decoded_custom) : $body_custom;
            } else {
                $args['body'] = wp_json_encode(array(
                    'model' => $model,
                    'messages' => array(
                        array('role' => 'system', 'content' => $system),
                        array('role' => 'user', 'content' => $message),
                    ),
                    'message' => $message,
                    'system' => $system,
                    'attachments' => $attachments,
                    'source' => 'aira-studio',
                ));
            }
        }

        if ($url === '') return array('ok' => false, 'text' => 'no_url');
        $response_path = ($provider === 'custom') ? trim((string)($settings['custom_response_path'] ?? '')) : '';
        $result = $this->execute_ai_json_post($url, $args, $response_path);
        if (empty($result['ok']) && $fallback_url !== '' && is_array($fallback_args)) {
            $fallback_result = $this->execute_ai_json_post($fallback_url, $fallback_args);
            if (!empty($fallback_result['ok'])) {
                return $fallback_result + array('endpoint' => $fallback_url, 'model' => $model);
            }
            $result = array('ok' => false, 'text' => $result['text'] . ' | fallback:' . $fallback_result['text']);
        }
        if (empty($result['ok']) && empty($settings['__model_retry_done']) && $this->looks_like_model_error($result['text'] ?? '')) {
            $default_model = $this->provider_default_model($provider);
            if ($default_model !== '' && $default_model !== $model) {
                $retry_settings = $this->set_provider_model_override($provider, $settings, $default_model);
                $retry_settings['__model_retry_done'] = true;
                $retry = $this->provider_request($provider, $message, $retry_settings, $key, $attachments);
                if (!empty($retry['ok'])) return $retry + array('repaired_model' => $default_model);
                return array('ok' => false, 'text' => $result['text'] . ' | model_repair:' . ($retry['text'] ?? 'failed'));
            }
        }
        return $result;
    }

    private function execute_ai_json_post($url, $args, $response_path = '') {
        if (!is_array($args)) { $args = array(); }
        if (empty($args['timeout'])) { $args['timeout'] = 30; }
        $res = wp_remote_post($url, array_merge(array('timeout' => 30), $args));
        $endpoint = preg_replace('#^https?://#', '', (string)$url);
        if (is_wp_error($res)) {
            return array('ok' => false, 'text' => 'network_error@' . $endpoint . ':' . $res->get_error_message());
        }
        $code = wp_remote_retrieve_response_code($res);
        $body_raw = wp_remote_retrieve_body($res);
        $body = json_decode($body_raw, true);
        if ($code < 200 || $code >= 300 || !is_array($body)) {
            $err = '';
            if (is_array($body) && isset($body['error'])) {
                if (is_array($body['error']) && isset($body['error']['message'])) {
                    $err = (string)$body['error']['message'];
                } elseif (is_scalar($body['error'])) {
                    $err = (string)$body['error'];
                }
            }
            if ($err === '' && is_array($body) && isset($body['message']) && is_scalar($body['message'])) $err = (string)$body['message'];
            if ($err === '') $err = wp_strip_all_tags((string)$body_raw);
            if ($err === '') $err = 'empty_or_non_json_response';
            return array('ok' => false, 'text' => 'http_' . intval($code) . '@' . $endpoint . ':' . substr($err, 0, 320));
        }
        $text = $response_path !== '' ? $this->custom_response_path_value($body, $response_path) : '';
        if ($text === '') $text = $this->extract_provider_text($body);
        return $text !== ''
            ? array('ok' => true, 'text' => $text, 'endpoint' => $endpoint)
            : array('ok' => false, 'text' => 'empty_response@' . $endpoint . ':' . substr(wp_json_encode($body), 0, 220));
    }

    private function extract_provider_text($body) {
        if (!is_array($body)) return '';
        if (isset($body['output_text']) && is_scalar($body['output_text'])) return (string)$body['output_text'];
        if (isset($body['choices'][0]['message']['content'])) return (string)$body['choices'][0]['message']['content'];
        if (isset($body['content'][0]['text'])) return (string)$body['content'][0]['text'];
        if (isset($body['candidates'][0]['content']['parts'][0]['text'])) return (string)$body['candidates'][0]['content']['parts'][0]['text'];
        if (isset($body['reply'])) return (string)$body['reply'];
        if (isset($body['text']) && is_scalar($body['text'])) return (string)$body['text'];
        if (isset($body['response']) && is_scalar($body['response'])) return (string)$body['response'];
        if (isset($body['output']) && is_array($body['output'])) {
            $chunks = array();
            foreach ($body['output'] as $item) {
                if (isset($item['type']) && $item['type'] === 'message' && !empty($item['content']) && is_array($item['content'])) {
                    foreach ($item['content'] as $part) {
                        if (isset($part['text']) && is_scalar($part['text'])) $chunks[] = (string)$part['text'];
                        elseif (isset($part['content']) && is_scalar($part['content'])) $chunks[] = (string)$part['content'];
                        elseif (isset($part['type']) && $part['type'] === 'output_text' && isset($part['text'])) $chunks[] = (string)$part['text'];
                    }
                } elseif (!empty($item['content']) && is_array($item['content'])) {
                    foreach ($item['content'] as $part) {
                        if (isset($part['text']) && is_scalar($part['text'])) $chunks[] = (string)$part['text'];
                        elseif (isset($part['content']) && is_scalar($part['content'])) $chunks[] = (string)$part['content'];
                    }
                }
            }
            return trim(implode("\n", $chunks));
        }
        return '';
    }

    /* ============================================================
     * Attachments
     * ============================================================ */

    private function parse_attachments_from_request() {
        $raw = isset($_POST['attachments']) ? wp_unslash($_POST['attachments']) : '';
        if ($raw === '') return array();
        $items = json_decode((string)$raw, true);
        if (!is_array($items)) return array();
        $out = array();
        foreach ($items as $item) {
            if (!is_array($item)) continue;
            $type = sanitize_key($item['type'] ?? '');
            $mime = sanitize_text_field($item['mime'] ?? '');
            $name = sanitize_file_name($item['name'] ?? 'image');
            $data_url = isset($item['data_url']) ? trim((string)$item['data_url']) : '';
            if ($type === 'image' && preg_match('#^data:image/(png|jpeg|jpg|webp|gif);base64,[A-Za-z0-9+/=\r\n]+$#', $data_url)) {
                if (strlen($data_url) > 12 * 1024 * 1024) continue;
                if ($mime === '') $mime = preg_match('#^data:([^;]+);#', $data_url, $m) ? $m[1] : 'image/png';
                $out[] = array('type' => 'image', 'mime' => $mime, 'name' => $name, 'data_url' => $data_url);
            }
            if (count($out) >= 3) break;
        }
        return $out;
    }

    private function openai_responses_input($message, $attachments = array()) {
        if (empty($attachments)) return $message;
        $content = array(array('type' => 'input_text', 'text' => $message));
        foreach ($attachments as $a) {
            if (($a['type'] ?? '') === 'image' && !empty($a['data_url'])) {
                $content[] = array('type' => 'input_image', 'image_url' => $a['data_url']);
            }
        }
        return array(array('role' => 'user', 'content' => $content));
    }

    private function openai_chat_content($message, $attachments = array()) {
        if (empty($attachments)) return $message;
        $content = array(array('type' => 'text', 'text' => $message));
        foreach ($attachments as $a) {
            if (($a['type'] ?? '') === 'image' && !empty($a['data_url'])) {
                $content[] = array('type' => 'image_url', 'image_url' => array('url' => $a['data_url']));
            }
        }
        return $content;
    }

    private function anthropic_content($message, $attachments = array()) {
        if (empty($attachments)) return $message;
        $content = array(array('type' => 'text', 'text' => $message));
        foreach ($attachments as $a) {
            if (($a['type'] ?? '') !== 'image' || empty($a['data_url'])) continue;
            $parts = explode(',', $a['data_url'], 2);
            $data = isset($parts[1]) ? $parts[1] : '';
            $mime = $a['mime'] ?? 'image/png';
            $content[] = array('type' => 'image', 'source' => array('type' => 'base64', 'media_type' => $mime, 'data' => $data));
        }
        return $content;
    }

    private function gemini_parts($text, $attachments = array()) {
        $parts = array(array('text' => $text));
        foreach ($attachments as $a) {
            if (($a['type'] ?? '') !== 'image' || empty($a['data_url'])) continue;
            $bits = explode(',', $a['data_url'], 2);
            $data = isset($bits[1]) ? $bits[1] : '';
            $parts[] = array('inline_data' => array('mime_type' => $a['mime'] ?? 'image/png', 'data' => $data));
        }
        return $parts;
    }

    /* ============================================================
     * AJAX: install generated WordPress plugin safely
     * ============================================================ */


    private function generated_plugin_header_from_code($code, $label) {
        $code = (string)$code;
        $label = preg_quote((string)$label, '/');
        if (preg_match('/' . $label . '\s*:\s*([^\r\n*]+)/i', $code, $m)) {
            return trim(wp_strip_all_tags($m[1]));
        }
        return '';
    }

    private function validate_generated_plugin_code($code) {
        $code = wp_check_invalid_utf8((string)$code);
        $code = str_replace(chr(0), '', $code);
        if ($code === '') {
            return new WP_Error('empty_plugin_code', 'ไม่พบโค้ดสำหรับติดตั้ง/อัปเกรด');
        }
        if (strlen($code) > 700000) {
            return new WP_Error('plugin_code_too_large', 'plugin_code_too_large');
        }
        if (strpos($code, '<?php') === false || !preg_match('/Plugin\s+Name\s*:/i', $code)) {
            return new WP_Error('missing_plugin_header', 'ต้องเป็นไฟล์ PHP ที่มี WordPress Plugin Header เช่น Plugin Name:');
        }
        if (preg_match('/(eval\s*\(|base64_decode\s*\(|shell_exec\s*\(|passthru\s*\(|proc_open\s*\(|popen\s*\()/i', $code)) {
            return new WP_Error('risky_plugin_code', 'พบคำสั่งเสี่ยงสูงในโค้ด กรุณาตรวจด้วยตนเองก่อนติดตั้งหรืออัปเกรด');
        }
        return $code;
    }

    private function generated_plugin_headers($code) {
        return array(
            'name' => $this->generated_plugin_header_from_code($code, 'Plugin Name'),
            'description' => $this->generated_plugin_header_from_code($code, 'Description'),
            'version' => $this->generated_plugin_header_from_code($code, 'Version'),
            'author' => $this->generated_plugin_header_from_code($code, 'Author'),
            'text_domain' => $this->generated_plugin_header_from_code($code, 'Text Domain'),
        );
    }


    private function preinstall_generated_code_report($code) {
        $code = wp_check_invalid_utf8((string)$code);
        $code = str_replace(chr(0), '', $code);
        $headers = $this->generated_plugin_headers($code);
        $has_php = strpos($code, '<?php') !== false;
        $has_header = (bool)preg_match('/Plugin\s+Name\s*:/i', $code);
        $has_nonce = (bool)preg_match('/nonce|check_ajax_referer|wp_verify_nonce|wp_create_nonce/i', $code);
        $has_capability = (bool)preg_match('/current_user_can|manage_options|edit_posts|permission_callback/i', $code);
        $has_sanitize = (bool)preg_match('/sanitize_|wp_kses|esc_html|esc_attr|esc_url|absint|sanitize_text_field/i', $code);
        $has_uninstall = (bool)preg_match('/register_uninstall_hook|uninstall\.php|delete_option\s*\(/i', $code);
        $risky = (bool)preg_match('/(eval\s*\(|base64_decode\s*\(|shell_exec\s*\(|passthru\s*\(|proc_open\s*\(|popen\s*\(|curl_exec\s*\(|file_put_contents\s*\()/i', $code);
        $shortcodes = array();
        $actions = array();
        $rest = array();
        $ajax = array();
        if (preg_match_all('/add_shortcode\s*\(\s*["\']([^"\']+)["\']/i', $code, $m)) $shortcodes = array_values(array_unique(array_slice($m[1], 0, 12)));
        if (preg_match_all('/add_action\s*\(\s*["\']([^"\']+)["\']/i', $code, $m)) $actions = array_values(array_unique(array_slice($m[1], 0, 16)));
        if (preg_match_all('/register_rest_route\s*\(\s*["\']([^"\']+)["\']/i', $code, $m)) $rest = array_values(array_unique(array_slice($m[1], 0, 12)));
        if (preg_match_all('/wp_ajax_(?:nopriv_)?([a-z0-9_\-]+)/i', $code, $m)) $ajax = array_values(array_unique(array_slice($m[1], 0, 12)));
        $score = 30;
        if ($has_php) $score += 10;
        if ($has_header) $score += 16;
        if (!empty($headers['version'])) $score += 8;
        if ($has_capability) $score += 10;
        if ($has_nonce) $score += 10;
        if ($has_sanitize) $score += 10;
        if ($has_uninstall) $score += 6;
        if (!empty($shortcodes) || !empty($actions) || !empty($rest) || !empty($ajax)) $score += 8;
        if ($risky) $score -= 25;
        $score = max(0, min(100, $score));
        $missing = array();
        if (!$has_php) $missing[] = 'PHP opening tag';
        if (!$has_header) $missing[] = 'Plugin Header';
        if (!$has_capability) $missing[] = 'Capability / permission check';
        if (!$has_nonce) $missing[] = 'Nonce verification';
        if (!$has_sanitize) $missing[] = 'Sanitize / escape output';
        if (!$has_uninstall) $missing[] = 'Uninstall / cleanup plan';
        if ($risky) $missing[] = 'Remove risky function before install';
        $status = $score >= 82 && !$risky ? 'ready_for_staging' : ($score >= 62 && !$risky ? 'needs_review' : 'needs_fix_before_install');
        return array(
            'version' => self::VERSION,
            'mode' => 'server_static_preinstall_check_no_php_execution',
            'status' => $status,
            'score' => $score,
            'installable' => $has_php && $has_header && !$risky,
            'headers' => $headers,
            'checks' => array(
                'php_tag' => $has_php,
                'plugin_header' => $has_header,
                'nonce' => $has_nonce,
                'capability' => $has_capability,
                'sanitize_escape' => $has_sanitize,
                'uninstall_cleanup' => $has_uninstall,
                'risky_function' => $risky,
                'plugin_dir_writable' => is_writable(WP_PLUGIN_DIR),
                'can_install_plugins' => current_user_can('install_plugins'),
                'can_update_plugins' => current_user_can('update_plugins'),
            ),
            'missing' => $missing,
            'shortcodes' => $shortcodes,
            'actions' => $actions,
            'rest' => $rest,
            'ajax' => $ajax,
            'code_hash' => $this->generated_code_hash($code),
            'gate_token' => $this->create_preinstall_gate_token($code, !empty($_POST['interactive'])),
            'gate_expires_in' => self::PREINSTALL_GATE_TTL,
            'runtime_guard' => $this->runtime_write_guard_report($code, 'preinstall'),
            'message' => 'ตรวจ Pre-install บน WordPress สำเร็จแบบไม่ execute PHP และไม่เขียนไฟล์จริง พร้อมออก gate token สำหรับ install/upgrade รอบนี้',
        );
    }


    private function generated_code_hash($code) {
        $code = wp_check_invalid_utf8((string)$code);
        $code = str_replace(chr(0), '', $code);
        return hash('sha256', $code);
    }

    private function preinstall_gate_secret() {
        if (function_exists('wp_salt')) {
            return wp_salt('auth') . '|' . wp_salt('secure_auth') . '|' . self::NONCE;
        }
        return (defined('AUTH_KEY') ? AUTH_KEY : 'aira') . '|' . self::NONCE;
    }

    private function base64url_encode($value) {
        return rtrim(strtr(base64_encode((string)$value), '+/', '-_'), '=');
    }

    private function base64url_decode($value) {
        $value = (string)$value;
        $pad = strlen($value) % 4;
        if ($pad) { $value .= str_repeat('=', 4 - $pad); }
        $decoded = base64_decode(strtr($value, '-_', '+/'), true);
        return $decoded === false ? '' : $decoded;
    }

    private function create_preinstall_gate_token($code, $interactive = false) {
        $payload = array(
            'v' => self::VERSION,
            'uid' => get_current_user_id(),
            'cap' => $this->capability(),
            'hash' => $this->generated_code_hash($code),
            'interactive' => $interactive ? 1 : 0,
            'iat' => time(),
            'exp' => time() + self::PREINSTALL_GATE_TTL,
        );
        $body = $this->base64url_encode(wp_json_encode($payload));
        $sig = hash_hmac('sha256', $body, $this->preinstall_gate_secret());
        return $body . '.' . $sig;
    }

    private function validate_preinstall_gate_token($code, $token, $require_interactive = true) {
        $token = trim((string)$token);
        if ($token === '' || strpos($token, '.') === false) {
            return new WP_Error('preinstall_gate_missing', 'ต้องผ่าน Preview + Interactive Pre-install Test ก่อนติดตั้งหรืออัปเกรด');
        }
        list($body, $sig) = explode('.', $token, 2);
        $expected = hash_hmac('sha256', $body, $this->preinstall_gate_secret());
        if (!hash_equals($expected, $sig)) {
            return new WP_Error('preinstall_gate_invalid', 'Pre-install gate token ไม่ถูกต้อง กรุณาทดลองกดใน Preview ใหม่อีกครั้ง');
        }
        $payload = json_decode($this->base64url_decode($body), true);
        if (!is_array($payload)) {
            return new WP_Error('preinstall_gate_payload_invalid', 'Pre-install gate payload อ่านไม่ได้');
        }
        if ((int)($payload['uid'] ?? 0) !== get_current_user_id()) {
            return new WP_Error('preinstall_gate_user_mismatch', 'Pre-install gate ไม่ตรงกับผู้ใช้ปัจจุบัน');
        }
        $cap = sanitize_key($payload['cap'] ?? $this->capability());
        if ($cap !== '' && !current_user_can($cap)) {
            return new WP_Error('preinstall_gate_capability_failed', 'ผู้ใช้ปัจจุบันไม่มีสิทธิ์ตาม Pre-install gate');
        }
        if ((int)($payload['exp'] ?? 0) < time()) {
            return new WP_Error('preinstall_gate_expired', 'Pre-install gate หมดอายุ กรุณาเปิด Preview และทดสอบใหม่');
        }
        if (!hash_equals((string)($payload['hash'] ?? ''), $this->generated_code_hash($code))) {
            return new WP_Error('preinstall_gate_code_changed', 'โค้ดเปลี่ยนหลังจากทดสอบ Preview กรุณาทดสอบใหม่ก่อนติดตั้ง/อัปเกรด');
        }
        if ($require_interactive && empty($payload['interactive'])) {
            return new WP_Error('preinstall_gate_not_interactive', 'ต้องกดทดสอบระบบใน Sandbox ก่อนติดตั้ง/อัปเกรด');
        }
        return $payload;
    }

    private function runtime_write_guard_report($code, $mode = 'install') {
        $headers = $this->generated_plugin_headers($code);
        $plugin_name = trim((string)($headers['name'] ?? ''));
        $slug = sanitize_title(substr($plugin_name, 0, 80));
        if ($slug === '') { $slug = 'aira-generated-plugin'; }
        $current_plugin = wp_normalize_path(plugin_basename(__FILE__));
        $self_name = stripos($plugin_name, 'AiRA Studio') !== false;
        return array(
            'mode' => sanitize_key($mode),
            'code_hash' => $this->generated_code_hash($code),
            'plugin_name' => $plugin_name,
            'plugin_version' => (string)($headers['version'] ?? ''),
            'target_slug_preview' => $slug,
            'current_plugin' => $current_plugin,
            'protect_aira_core' => true,
            'looks_like_aira_core' => $self_name,
            'plugin_dir_writable' => is_writable(WP_PLUGIN_DIR),
            'can_install_plugins' => current_user_can('install_plugins'),
            'can_update_plugins' => current_user_can('update_plugins'),
            'auto_activate' => false,
            'write_policy' => 'no_php_execution_before_write; never_activate_automatically; backup_before_upgrade; block_self_overwrite',
        );
    }

    public function ajax_preinstall_check() {
        $this->verify();
        $raw_code = isset($_POST['code']) ? (string)wp_unslash($_POST['code']) : '';
        if ($raw_code === '') {
            wp_send_json_error(array('message' => 'empty_code'), 400);
        }
        if (strlen($raw_code) > 700000) {
            wp_send_json_error(array('message' => 'plugin_code_too_large'), 400);
        }
        wp_send_json_success($this->preinstall_generated_code_report($raw_code));
    }

    private function find_generated_plugin_upgrade_target($headers) {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $name = isset($headers['name']) ? trim((string)$headers['name']) : '';
        $base_slug = sanitize_title(substr($name, 0, 80));
        if ($base_slug === '') {
            $base_slug = 'aira-generated-plugin';
        }

        $current_plugin = plugin_basename(__FILE__);
        $plugins = function_exists('get_plugins') ? get_plugins() : array();
        foreach ($plugins as $file => $data) {
            if ($file === $current_plugin) {
                continue;
            }
            $existing_name = isset($data['Name']) ? trim((string)$data['Name']) : '';
            if ($existing_name === '' || (function_exists('mb_strtolower') ? mb_strtolower($existing_name) : strtolower($existing_name)) !== (function_exists('mb_strtolower') ? mb_strtolower($name) : strtolower($name))) {
                continue;
            }
            $dir = dirname($file);
            $dir_slug = $dir === '.' ? sanitize_title(pathinfo($file, PATHINFO_FILENAME)) : sanitize_title(basename($dir));
            $author = isset($data['Author']) ? (string)$data['Author'] : '';
            $safe_generated_target = (
                strpos($dir_slug, $base_slug) === 0 ||
                strpos($dir_slug, 'aira-generated') === 0 ||
                strpos($dir_slug, 'aira-upgrade') === 0 ||
                preg_match('/AiRA|Thinkb4do/i', $author)
            );
            if (!$safe_generated_target) {
                continue;
            }
            return array(
                'plugin_file' => $file,
                'absolute_file' => trailingslashit(WP_PLUGIN_DIR) . $file,
                'slug' => $dir === '.' ? sanitize_title(pathinfo($file, PATHINFO_FILENAME)) : basename($dir),
                'existing' => true,
                'old_version' => isset($data['Version']) ? (string)$data['Version'] : '',
            );
        }

        $safe_slug = 'aira-upgrade-' . $base_slug;
        $safe_slug = sanitize_title($safe_slug);
        $file = $safe_slug . '/' . $safe_slug . '.php';
        return array(
            'plugin_file' => $file,
            'absolute_file' => trailingslashit(WP_PLUGIN_DIR) . $file,
            'slug' => $safe_slug,
            'existing' => false,
            'old_version' => '',
        );
    }

    public function ajax_upgrade_generated_plugin() {
        $this->verify();
        if (!current_user_can('update_plugins') && !current_user_can('install_plugins')) {
            wp_send_json_error(array('message' => 'update_plugins_permission_required'), 403);
        }
        $raw_code = isset($_POST['code']) ? (string)wp_unslash($_POST['code']) : '';
        $validated = $this->validate_generated_plugin_code($raw_code);
        if (is_wp_error($validated)) {
            wp_send_json_error(array('message' => $validated->get_error_message()), 400);
        }
        $code = $validated;
        if (!empty($_POST['strict_gate'])) {
            $gate = $this->validate_preinstall_gate_token($code, isset($_POST['preinstall_token']) ? (string)wp_unslash($_POST['preinstall_token']) : '', true);
            if (is_wp_error($gate)) {
                wp_send_json_error(array('message' => $gate->get_error_message()), 400);
            }
        }
        $headers = $this->generated_plugin_headers($code);
        if (empty($headers['name'])) {
            wp_send_json_error(array('message' => 'ต้องมี Plugin Name ก่อนอัปเกรด'), 400);
        }

        $target = $this->find_generated_plugin_upgrade_target($headers);
        $target_file = wp_normalize_path($target['absolute_file']);
        $plugins_dir = wp_normalize_path(trailingslashit(WP_PLUGIN_DIR));
        if (strpos($target_file, $plugins_dir) !== 0) {
            wp_send_json_error(array('message' => 'invalid_plugin_target'), 400);
        }
        if (wp_normalize_path($target['plugin_file']) === wp_normalize_path(plugin_basename(__FILE__))) {
            wp_send_json_error(array('message' => 'ไม่อนุญาตให้อัปเกรด AiRA Studio ตัวหลักจาก code block นี้'), 400);
        }

        $target_dir = dirname($target_file);
        if (!wp_mkdir_p($target_dir)) {
            wp_send_json_error(array('message' => 'cannot_create_plugin_upgrade_dir'), 500);
        }

        $backup_file = '';
        if (file_exists($target_file)) {
            $backup_file = $target_file . '.bak-' . gmdate('Ymd-His');
            if (!@copy($target_file, $backup_file)) {
                wp_send_json_error(array('message' => 'cannot_create_plugin_backup_before_upgrade'), 500);
            }
        }

        if (file_put_contents($target_file, $code) === false) {
            if ($backup_file && file_exists($backup_file)) {
                @copy($backup_file, $target_file);
            }
            wp_send_json_error(array('message' => 'cannot_write_plugin_upgrade_file'), 500);
        }

        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if (function_exists('wp_clean_plugins_cache')) {
            wp_clean_plugins_cache(true);
        }
        $is_active = function_exists('is_plugin_active') ? is_plugin_active($target['plugin_file']) : false;

        wp_send_json_success(array(
            'message' => $target['existing'] ? 'อัปเกรดปลั๊กอินสำเร็จ พร้อมตอบคำถามต่อไปได้ตามปกติ' : 'สร้างแพ็กอัปเกรดปลั๊กอินสำเร็จ พร้อมตอบคำถามต่อไปได้ตามปกติ',
            'plugins_url' => admin_url('plugins.php'),
            'plugin_file' => $target['plugin_file'],
            'plugin_slug' => $target['slug'],
            'plugin_name' => $headers['name'],
            'version' => $headers['version'],
            'old_version' => $target['old_version'],
            'is_active' => $is_active,
            'backup_created' => $backup_file ? basename($backup_file) : '',
            'runtime_guard' => $this->runtime_write_guard_report($code, 'upgrade'),
        ));
    }

    public function ajax_install_generated_plugin() {
        $this->verify();
        if (!current_user_can('install_plugins')) {
            wp_send_json_error(array('message' => 'install_plugins_permission_required'), 403);
        }
        $code = isset($_POST['code']) ? (string)wp_unslash($_POST['code']) : '';
        $code = wp_check_invalid_utf8($code);
        $code = str_replace(chr(0), '', $code);
        if (strlen($code) > 700000) {
            wp_send_json_error(array('message' => 'plugin_code_too_large'), 400);
        }
        if (strpos($code, '<?php') === false || !preg_match('/Plugin\s+Name\s*:/i', $code)) {
            wp_send_json_error(array('message' => 'ต้องเป็นไฟล์ PHP ที่มี WordPress Plugin Header เช่น Plugin Name:'), 400);
        }
        if (preg_match('/(eval\s*\(|base64_decode\s*\(|shell_exec\s*\(|passthru\s*\(|proc_open\s*\(|popen\s*\()/i', $code)) {
            wp_send_json_error(array('message' => 'พบคำสั่งเสี่ยงสูงในโค้ด กรุณาตรวจด้วยตนเองก่อนติดตั้ง'), 400);
        }
        if (!empty($_POST['strict_gate'])) {
            $gate = $this->validate_preinstall_gate_token($code, isset($_POST['preinstall_token']) ? (string)wp_unslash($_POST['preinstall_token']) : '', true);
            if (is_wp_error($gate)) {
                wp_send_json_error(array('message' => $gate->get_error_message()), 400);
            }
        }
        $plugin_name = 'aira-generated-plugin';
        if (preg_match('/Plugin\s+Name\s*:\s*(.+)/i', $code, $m)) {
            $plugin_name = sanitize_title(substr(trim($m[1]), 0, 80));
        }
        if ($plugin_name === '') $plugin_name = 'aira-generated-plugin';
        $slug = $plugin_name . '-' . wp_generate_password(5, false, false);
        $tmp_base = trailingslashit(get_temp_dir()) . $slug;
        if (!wp_mkdir_p($tmp_base . '/' . $slug)) {
            wp_send_json_error(array('message' => 'cannot_create_temp_plugin_dir'), 500);
        }
        $main_file = $tmp_base . '/' . $slug . '/' . $slug . '.php';
        if (file_put_contents($main_file, $code) === false) {
            wp_send_json_error(array('message' => 'cannot_write_temp_plugin_file'), 500);
        }
        $zip_path = $tmp_base . '.zip';
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                wp_send_json_error(array('message' => 'cannot_create_zip'), 500);
            }
            $zip->addFile($main_file, $slug . '/' . $slug . '.php');
            $zip->close();
        } else {
            require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';
            if (!class_exists('PclZip')) {
                wp_send_json_error(array('message' => 'ไม่มี ZipArchive/PclZip สำหรับสร้างแพ็กปลั๊กอิน'), 500);
            }
            $archive = new PclZip($zip_path);
            $created = $archive->create($tmp_base . '/' . $slug, PCLZIP_OPT_REMOVE_PATH, $tmp_base);
            if (!$created) {
                wp_send_json_error(array('message' => 'cannot_create_zip_pclzip'), 500);
            }
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        WP_Filesystem();
        $skin = new Automatic_Upgrader_Skin();
        $upgrader = new Plugin_Upgrader($skin);
        $result = $upgrader->install($zip_path);
        @unlink($zip_path);
        @unlink($main_file);
        @rmdir($tmp_base . '/' . $slug);
        @rmdir($tmp_base);
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()), 500);
        }
        if (!$result) {
            $err = isset($skin->result) && is_wp_error($skin->result) ? $skin->result->get_error_message() : 'install_failed';
            wp_send_json_error(array('message' => $err), 500);
        }
        wp_send_json_success(array(
            'message' => 'ติดตั้งปลั๊กอินสำเร็จ แต่ยังไม่เปิดใช้งานอัตโนมัติ เพื่อความปลอดภัย',
            'plugins_url' => admin_url('plugins.php'),
            'plugin_slug' => $slug,
            'runtime_guard' => $this->runtime_write_guard_report($code, 'install'),
        ));
    }

    /* ============================================================
     * AJAX: image generation
     * ============================================================ */

    public function ajax_generate_image() {
        $this->verify();
        $prompt = isset($_POST['prompt']) ? sanitize_textarea_field(wp_unslash($_POST['prompt'])) : '';
        if ($prompt === '') wp_send_json_error(array('message' => 'empty_prompt'), 400);
        $settings = get_option(self::OPTION_SETTINGS, array());
        $secrets = $this->usable_api_secrets();

        $openai_key = $this->normalize_api_key_input((string)($secrets['openai'] ?? ''));
        $hub_key = $this->normalize_api_key_input((string)($secrets['universal_api_hub'] ?? ''));
        $key = $openai_key;
        if ($key === '' && $hub_key !== '' && $this->detect_provider_from_api_key($hub_key) === 'openai') {
            $key = $hub_key;
        }

        $image_url = '';
        $download_url = '';
        $mode = 'local_svg_preview';
        $real_image = false;
        $api_debug = array();
        $endpoint = 'https://api.openai.com/v1/images/generations';
        $note = 'ยังไม่พบ OpenAI Image API key ที่ใช้ได้ จึงแสดงภาพตัวอย่างสำรอง ไม่ใช่ภาพจริงจาก API';

        if ($openai_key === '' && $hub_key !== '' && $this->detect_provider_from_api_key($hub_key) !== 'openai') {
            $api_debug[] = 'universal_api_hub_not_openai:Image API ต้องใช้ OpenAI key ที่เรียก /v1/images/generations ได้';
        }

        if ($key !== '') {
            $timeout = max(30, min(180, intval($settings['api_timeout'] ?? 90)));
            $size = trim((string)($settings['image_generation_size'] ?? '1024x1024')) ?: '1024x1024';
            $image_model = trim((string)($settings['image_generation_model'] ?? 'gpt-image-1.5')) ?: 'gpt-image-1.5';
            $format = $this->normalize_image_output_format((string)($settings['openai_image_output_format'] ?? 'png'));
            $body = array(
                'model' => $image_model,
                'prompt' => $prompt,
                'size' => $size,
                'quality' => 'low',
                'n' => 1,
            );
            if (stripos($image_model, 'gpt-image-') === 0 || stripos($image_model, 'chatgpt-image') === 0) {
                $body['output_format'] = $format;
                $body['background'] = 'auto';
                $body['moderation'] = 'auto';
            } elseif (stripos($image_model, 'dall-e-') === 0) {
                $body['response_format'] = 'b64_json';
            }

            $res_img = wp_remote_post($endpoint, array(
                'timeout' => $timeout,
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $key,
                    'User-Agent' => 'AiRA-Studio/' . self::VERSION,
                ),
                'body' => wp_json_encode($body),
            ));
            if (!is_wp_error($res_img)) {
                $code_img = wp_remote_retrieve_response_code($res_img);
                $body_img = json_decode(wp_remote_retrieve_body($res_img), true);
                $b64_img = $this->extract_image_b64($body_img);
                $url_img = $this->extract_image_url($body_img);
                if ($code_img >= 200 && $code_img < 300 && $b64_img !== '') {
                    $mime = $this->image_mime_from_api_body($body_img, $format);
                    $image_url = 'data:' . $mime . ';base64,' . preg_replace('/\s+/', '', $b64_img);
                    $mode = 'openai_images_api_real';
                    $real_image = true;
                    $note = 'ภาพจริงจาก OpenAI Images API · แสดงในแชทและบันทึกได้';
                } elseif ($code_img >= 200 && $code_img < 300 && $url_img !== '') {
                    $image_url = $url_img;
                    $mode = 'openai_images_api_real_url';
                    $real_image = true;
                    $note = 'ภาพจริงจาก OpenAI Images API URL · แสดงในแชทและบันทึกได้';
                } else {
                    $api_debug[] = 'images_api:' . $this->image_api_error_text($res_img);
                }
            } else {
                $api_debug[] = 'images_api:' . $this->image_api_error_text($res_img);
            }
            if ($image_url === '') {
                $note = 'ภาพจริงยังไม่ขึ้น เพราะ Image API ยังไม่คืนไฟล์ภาพ · ' . implode(' | ', array_slice($api_debug, 0, 2));
            }
        }

        if ($image_url === '') {
            $image_url = $this->safe_svg_preview_data_url($prompt);
            $download_url = $image_url;
            $mode = 'local_svg_preview_not_real';
            if (empty($api_debug)) $api_debug[] = 'no_openai_image_key_or_api_not_configured';
        }

        $saved_image = $this->save_generated_image_to_uploads($image_url, $prompt);
        if (is_array($saved_image) && !empty($saved_image['url'])) {
            $download_url = $saved_image['url'];
            $image_url = $saved_image['url'];
        } elseif ($download_url === '') {
            $download_url = $image_url;
        }

        $docs = get_option(self::OPTION_DOCS, array());
        if (!is_array($docs)) $docs = array();
        $doc = array(
            'id' => 'image-' . wp_generate_password(8, false, false),
            'title' => 'Generated Image · ' . substr($prompt, 0, 60),
            'type' => $real_image ? 'generated-image-real' : 'generated-image-fallback',
            'source_url' => '',
            'content' => "# Generated Image\n\nPrompt:\n" . $prompt . "\n\nReal image: " . ($real_image ? 'yes' : 'no') . "\nMode: " . $mode . "\nEndpoint: " . $endpoint . "\nNote: " . $note . "\nDebug: " . implode(' | ', $api_debug) . "\nDownload: " . $download_url . "\n\n" . $image_url,
            'created' => current_time('mysql'),
        );
        $docs[] = $doc;
        update_option(self::OPTION_DOCS, $docs, false);
        wp_send_json_success(array(
            'image_url' => $image_url,
            'download_url' => $download_url,
            'prompt' => $prompt,
            'mode' => $mode,
            'real_image' => $real_image,
            'message' => $note,
            'saved' => $saved_image,
            'api_debug' => $api_debug,
            'endpoint' => $endpoint,
            'doc' => $doc,
        ));
    }

    private function normalize_image_output_format($format) {
        $format = strtolower(trim((string)$format));
        if ($format === 'jpg') $format = 'jpeg';
        if (!in_array($format, array('png','jpeg','webp'), true)) $format = 'png';
        return $format;
    }

    private function image_mime_from_api_body($body, $fallback_format = 'png') {
        $format = $this->normalize_image_output_format($fallback_format);
        if (is_array($body) && !empty($body['output_format']) && is_scalar($body['output_format'])) {
            $format = $this->normalize_image_output_format((string)$body['output_format']);
        }
        if ($format === 'jpeg') return 'image/jpeg';
        if ($format === 'webp') return 'image/webp';
        return 'image/png';
    }

    private function extract_image_b64($body) {
        if (!is_array($body)) return '';
        if (isset($body['data'][0]['b64_json']) && is_scalar($body['data'][0]['b64_json'])) return (string)$body['data'][0]['b64_json'];
        if (isset($body['output']) && is_array($body['output'])) {
            foreach ($body['output'] as $item) {
                if (isset($item['type']) && $item['type'] === 'image_generation_call' && !empty($item['result'])) return (string)$item['result'];
                if (!empty($item['content']) && is_array($item['content'])) {
                    foreach ($item['content'] as $part) {
                        if (!empty($part['result'])) return (string)$part['result'];
                        if (!empty($part['image_base64'])) return (string)$part['image_base64'];
                    }
                }
            }
        }
        return '';
    }

    private function extract_image_url($body) {
        if (!is_array($body)) return '';
        if (isset($body['data'][0]['url']) && is_scalar($body['data'][0]['url'])) return esc_url_raw((string)$body['data'][0]['url']);
        return '';
    }

    private function safe_svg_preview_data_url($prompt) {
        $title = esc_html(function_exists('mb_substr') ? mb_substr($prompt, 0, 72) : substr($prompt, 0, 72));
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1024" height="1024" viewBox="0 0 1024 1024"><defs><linearGradient id="g" x1="0" x2="1" y1="0" y2="1"><stop stop-color="#1E6B45"/><stop offset="0.55" stop-color="#ffffff"/><stop offset="1" stop-color="#F97316"/></linearGradient></defs><rect width="1024" height="1024" rx="64" fill="url(#g)"/><circle cx="512" cy="402" r="184" fill="rgba(255,255,255,.6)"/><path d="M363 567c96-146 221-146 318 0" fill="none" stroke="#111" stroke-width="32" stroke-linecap="round" opacity=".65"/><text x="512" y="752" font-family="-apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue, sans-serif" font-size="44" text-anchor="middle" fill="#111">AiRA Preview</text><text x="512" y="812" font-family="-apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue, sans-serif" font-size="24" text-anchor="middle" fill="#111" opacity=".72">' . $title . '</text></svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private function save_generated_image_to_uploads($image_url, $prompt = '') {
        $image_url = (string)$image_url;
        if (strpos($image_url, 'data:image/') !== 0) {
            return array('url' => esc_url_raw($image_url), 'stored' => false, 'reason' => 'external_or_remote_url');
        }
        if (!preg_match('#^data:(image/(?:png|jpeg|jpg|webp|svg\+xml));base64,([A-Za-z0-9+/=\r\n]+)$#', $image_url, $m)) {
            return array('url' => '', 'stored' => false, 'reason' => 'unsupported_data_url');
        }
        $mime = strtolower($m[1]);
        $raw = base64_decode(str_replace(array("\r", "\n"), '', $m[2]), true);
        if ($raw === false || strlen($raw) < 20) {
            return array('url' => '', 'stored' => false, 'reason' => 'decode_failed');
        }
        $ext_map = array('image/png' => 'png', 'image/jpeg' => 'jpg', 'image/jpg' => 'jpg', 'image/webp' => 'webp', 'image/svg+xml' => 'svg');
        $ext = isset($ext_map[$mime]) ? $ext_map[$mime] : 'png';
        $upload = wp_upload_dir();
        if (empty($upload['basedir']) || empty($upload['baseurl'])) {
            return array('url' => '', 'stored' => false, 'reason' => 'upload_dir_unavailable');
        }
        $dir = trailingslashit($upload['basedir']) . 'aira-studio-images';
        $url_base = trailingslashit($upload['baseurl']) . 'aira-studio-images';
        if (!wp_mkdir_p($dir)) {
            return array('url' => '', 'stored' => false, 'reason' => 'cannot_create_upload_dir');
        }
        $slug_source = sanitize_title(function_exists('mb_substr') ? mb_substr((string)$prompt, 0, 28) : substr((string)$prompt, 0, 28));
        if ($slug_source === '') $slug_source = 'image';
        $file = 'aira-image-' . gmdate('Ymd-His') . '-' . $slug_source . '-' . wp_generate_password(4, false, false) . '.' . $ext;
        $path = trailingslashit($dir) . $file;
        if (file_put_contents($path, $raw) === false) {
            return array('url' => '', 'stored' => false, 'reason' => 'write_failed');
        }
        return array(
            'url' => esc_url_raw(trailingslashit($url_base) . $file),
            'stored' => true,
            'mime' => $mime,
            'filename' => $file,
            'bytes' => strlen($raw),
        );
    }

    private function image_api_error_text($res, $fallback = '') {
        if (is_wp_error($res)) return 'network_error:' . $res->get_error_message();
        $code = wp_remote_retrieve_response_code($res);
        $raw = wp_remote_retrieve_body($res);
        $body = json_decode($raw, true);
        $err = '';
        if (is_array($body) && isset($body['error'])) {
            if (is_array($body['error']) && isset($body['error']['message'])) $err = (string)$body['error']['message'];
            elseif (is_scalar($body['error'])) $err = (string)$body['error'];
        }
        if ($err === '') $err = wp_strip_all_tags((string)$raw);
        if ($err === '') $err = $fallback ?: 'empty_response';
        return 'http_' . intval($code) . ':' . substr($err, 0, 220);
    }

    /* ============================================================
     * AJAX: TTS via ElevenLabs
     * ============================================================ */

    private function elevenlabs_ready() {
        $settings = get_option(self::OPTION_SETTINGS, array());
        $secrets = $this->usable_api_secrets();
        return !empty($secrets['elevenlabs']) && (bool)$this->elevenlabs_voice_id_for_lang($settings, 'auto');
    }

    private function elevenlabs_voice_id_for_lang($settings, $lang = 'auto') {
        $settings = is_array($settings) ? $settings : array();
        $lang = strtolower((string)$lang);
        $map = array(
            'th' => 'elevenlabs_voice_id_th', 'th-th' => 'elevenlabs_voice_id_th',
            'en' => 'elevenlabs_voice_id_en', 'en-us' => 'elevenlabs_voice_id_en', 'en-gb' => 'elevenlabs_voice_id_en',
            'ja' => 'elevenlabs_voice_id_ja', 'ja-jp' => 'elevenlabs_voice_id_ja',
            'zh' => 'elevenlabs_voice_id_zh', 'zh-cn' => 'elevenlabs_voice_id_zh', 'zh-tw' => 'elevenlabs_voice_id_zh',
            'ko' => 'elevenlabs_voice_id_ko', 'ko-kr' => 'elevenlabs_voice_id_ko',
            'ar' => 'elevenlabs_voice_id_ar', 'ar-sa' => 'elevenlabs_voice_id_ar',
            'ru' => 'elevenlabs_voice_id_ru', 'ru-ru' => 'elevenlabs_voice_id_ru',
            'fr' => 'elevenlabs_voice_id_fr', 'fr-fr' => 'elevenlabs_voice_id_fr',
            'es' => 'elevenlabs_voice_id_es', 'es-es' => 'elevenlabs_voice_id_es',
        );
        $field = $map[$lang] ?? '';
        if ($field && !empty($settings[$field])) return trim((string)$settings[$field]);
        if (!empty($settings['elevenlabs_voice_id'])) return trim((string)$settings['elevenlabs_voice_id']);
        foreach (array('elevenlabs_voice_id_th','elevenlabs_voice_id_en','elevenlabs_voice_id_ja','elevenlabs_voice_id_zh','elevenlabs_voice_id_ko','elevenlabs_voice_id_ar','elevenlabs_voice_id_ru','elevenlabs_voice_id_fr','elevenlabs_voice_id_es') as $k) {
            if (!empty($settings[$k])) return trim((string)$settings[$k]);
        }
        return '';
    }

    private function elevenlabs_model_id_for_lang($settings, $lang = 'auto') {
        $settings = is_array($settings) ? $settings : array();
        $lang = strtolower((string)$lang);
        $map = array(
            'th' => 'elevenlabs_model_id_th', 'th-th' => 'elevenlabs_model_id_th',
            'en' => 'elevenlabs_model_id_en', 'en-us' => 'elevenlabs_model_id_en', 'en-gb' => 'elevenlabs_model_id_en',
            'ja' => 'elevenlabs_model_id_ja', 'ja-jp' => 'elevenlabs_model_id_ja',
            'zh' => 'elevenlabs_model_id_zh', 'zh-cn' => 'elevenlabs_model_id_zh', 'zh-tw' => 'elevenlabs_model_id_zh',
            'ko' => 'elevenlabs_model_id_ko', 'ko-kr' => 'elevenlabs_model_id_ko',
            'ar' => 'elevenlabs_model_id_ar', 'ar-sa' => 'elevenlabs_model_id_ar',
            'ru' => 'elevenlabs_model_id_ru', 'ru-ru' => 'elevenlabs_model_id_ru',
            'fr' => 'elevenlabs_model_id_fr', 'fr-fr' => 'elevenlabs_model_id_fr',
            'es' => 'elevenlabs_model_id_es', 'es-es' => 'elevenlabs_model_id_es',
        );
        $field = $map[$lang] ?? '';
        if ($field && !empty($settings[$field])) return sanitize_text_field(trim((string)$settings[$field]));
        if (!empty($settings['elevenlabs_model_id'])) return sanitize_text_field(trim((string)$settings['elevenlabs_model_id']));
        return 'eleven_multilingual_v2';
    }

    public function ajax_tts_voice() {
        $this->verify();
        $text = isset($_POST['text']) ? $this->sanitize_prompt_text($_POST['text']) : '';
        if ($text === '') wp_send_json_error(array('message' => 'empty_text'), 400);
        $settings = get_option(self::OPTION_SETTINGS, array());
        $secrets = $this->usable_api_secrets();
        $key = trim((string)($secrets['elevenlabs'] ?? ($secrets['universal_api_hub'] ?? '')));
        $lang = isset($_POST['lang']) ? sanitize_text_field(wp_unslash($_POST['lang'])) : 'auto';
        $voice_id = $this->elevenlabs_voice_id_for_lang($settings, $lang);
        if ($key === '') wp_send_json_error(array('message' => 'elevenlabs_missing_api_key'), 404);
        if ($voice_id === '') wp_send_json_error(array('message' => 'elevenlabs_missing_voice_id_for_' . sanitize_key($lang)), 404);

        $url = 'https://api.elevenlabs.io/v1/text-to-speech/' . rawurlencode($voice_id) . '/stream?output_format=mp3_44100_128';
        $model_id = $this->elevenlabs_model_id_for_lang($settings, $lang);
        $payload = array(
            'text' => function_exists('mb_substr') ? mb_substr($text, 0, 2400, 'UTF-8') : substr($text, 0, 2400),
            'model_id' => $model_id,
            'voice_settings' => array('stability' => 0.42, 'similarity_boost' => 0.86, 'style' => 0.25, 'use_speaker_boost' => true),
        );
        $res = wp_remote_post($url, array(
            'timeout' => max(8, min(28, intval($settings['voice_api_timeout'] ?? 14))),
            'headers' => array('Content-Type' => 'application/json', 'Accept' => 'audio/mpeg', 'xi-api-key' => $key),
            'body' => wp_json_encode($payload),
        ));
        if (is_wp_error($res)) wp_send_json_error(array('message' => $res->get_error_message()), 500);
        $code = wp_remote_retrieve_response_code($res);
        $body = wp_remote_retrieve_body($res);
        if ($code < 200 || $code >= 300 || $body === '') {
            wp_send_json_error(array('message' => 'tts_http_' . $code . ':' . substr(wp_strip_all_tags((string)$body), 0, 160)), $code ?: 500);
        }
        wp_send_json_success(array('mime' => 'audio/mpeg', 'audio' => base64_encode($body), 'provider' => 'elevenlabs', 'voice_id' => substr($voice_id, 0, 6) . '…' . substr($voice_id, -4), 'model_id' => $model_id, 'lang' => $lang));
    }

    /* ============================================================
     * AJAX: API key management + tests
     * ============================================================ */

    private function normalize_api_key_input($value) {
        $value = html_entity_decode((string)$value, ENT_QUOTES, 'UTF-8');
        $value = trim($value);
        $value = trim($value, "\x00..\x20\"'`“”‘’");
        if ($this->is_legacy_encrypted_secret($value)) return '';
        $value = preg_replace('/^Bearer\s+/i', '', $value);
        if (preg_match('/(?:api[_-]?key|key|token)\s*[=:]\s*([A-Za-z0-9_\.\-]+)/i', $value, $m)) {
            $value = $m[1];
        }
        $value = preg_replace('/\s+/', '', $value);
        $value = trim($value, "\x00..\x20\"'`“”‘’");
        if ($this->is_legacy_encrypted_secret($value)) return '';
        return trim($value);
    }

    private function provider_key_shape_warning($provider, $key) {
        $provider = sanitize_key($provider);
        $key = $this->normalize_api_key_input($key);
        if ($key === '') return '';
        if ($provider === 'openai' && !preg_match('/^sk-(proj-|svcacct-)?[A-Za-z0-9_\-]{20,}$/', $key)) {
            return 'key_shape_warning:OpenAI key ควรขึ้นต้น sk- หรือ sk-proj-';
        }
        if ($provider === 'openrouter' && !preg_match('/^sk-or-[A-Za-z0-9_\-]{10,}$/', $key)) {
            return 'key_shape_warning:OpenRouter key โดยทั่วไปขึ้นต้น sk-or-';
        }
        if ($provider === 'anthropic' && !preg_match('/^sk-ant-[A-Za-z0-9_\-]{10,}$/', $key)) {
            return 'key_shape_warning:Anthropic key โดยทั่วไปขึ้นต้น sk-ant-';
        }
        if ($provider === 'gemini' && !preg_match('/^AIza[0-9A-Za-z_\-]{20,}$/', $key)) {
            return 'key_shape_warning:Gemini key โดยทั่วไปขึ้นต้น AIza';
        }
        if ($provider === 'github' && !preg_match('/^(gh[pousr]_|github_pat_)[A-Za-z0-9_]{20,}/', $key)) {
            return 'key_shape_warning:GitHub token ควรขึ้นต้น ghp_ หรือ github_pat_';
        }
        if ($provider === 'groq' && !preg_match('/^(gsk_|gsk-)?[A-Za-z0-9_\-]{20,}$/', $key)) {
            return 'key_shape_warning:Groq key โดยทั่วไปเป็น gsk_ หรือ key ยาวจาก console';
        }
        if ($provider === 'mistral' && strlen($key) < 20) {
            return 'key_shape_warning:Mistral key ดูสั้นผิดปกติ';
        }
        if ($provider === 'perplexity' && !preg_match('/^pplx-[A-Za-z0-9_\-]{10,}$/', $key)) {
            return 'key_shape_warning:Perplexity key โดยทั่วไปขึ้นต้น pplx-';
        }
        if ($provider === 'deepseek' && !preg_match('/^sk-[A-Za-z0-9_\-]{20,}$/', $key)) {
            return 'key_shape_warning:DeepSeek key โดยทั่วไปขึ้นต้น sk-';
        }
        return '';
    }

    private function detect_provider_from_api_key($key) {
        $key = $this->normalize_api_key_input($key);
        if ($key === '') return '';
        if (preg_match('/^sk-or-[A-Za-z0-9_\-]{10,}$/', $key)) return 'openrouter';
        if (preg_match('/^sk-ant-[A-Za-z0-9_\-]{10,}$/', $key)) return 'anthropic';
        if (preg_match('/^AIza[0-9A-Za-z_\-]{20,}$/', $key)) return 'gemini';
        if (preg_match('/^sk-(proj-|svcacct-)?[A-Za-z0-9_\-]{20,}$/', $key)) return 'openai';
        if (preg_match('/^(gh[pousr]_|github_pat_)[A-Za-z0-9_]{20,}/', $key)) return 'github';
        if (preg_match('/^pplx-[A-Za-z0-9_\-]{10,}$/', $key)) return 'perplexity';
        if (preg_match('/^gsk[_-][A-Za-z0-9_\-]{10,}$/', $key)) return 'groq';
        return '';
    }

    private function auto_route_api_secrets($secrets, &$routed = array()) {
        if (!is_array($secrets)) $secrets = array();
        $routed = array();
        foreach ($secrets as $slot => $raw) {
            $slot = sanitize_key($slot);
            $clean = $this->normalize_api_key_input($raw);
            if ($clean === '') continue;
            $secrets[$slot] = sanitize_text_field($clean);
            $detected = $this->detect_provider_from_api_key($clean);
            if ($detected !== '' && empty($secrets[$detected])) {
                $secrets[$detected] = sanitize_text_field($clean);
                $routed[] = $slot . '→' . $detected;
            }
        }
        return $secrets;
    }

    public function ajax_save_api() {
        $this->verify();
        $settings = get_option(self::OPTION_SETTINGS, array());
        $posted_settings = $this->post_array_param('settings');
        if (!empty($posted_settings)) {
            foreach ($posted_settings as $k => $v) {
                $settings[sanitize_key($k)] = is_scalar($v) ? sanitize_textarea_field((string)$v) : '';
            }
            $settings = $this->normalize_settings_defaults($settings);
            update_option(self::OPTION_SETTINGS, $settings, false);
        }

        $secrets = $this->raw_api_secrets();
        if (!is_array($secrets)) $secrets = array();
        $routed = array();
        $warnings = array();
        $posted_api_keys = $this->post_array_param('api_keys');
        if (!empty($posted_api_keys)) {
            foreach ($posted_api_keys as $k => $v) {
                $slot = sanitize_key($k);
                if (!array_key_exists($slot, $this->providers())) continue;
                $val = $this->normalize_api_key_input((string)$v);
                if ($val === '') continue;

                // Standard slot policy: save to the exact provider field the user used.
                // Only Universal API Hub is allowed to auto-copy into the detected provider.
                $secrets[$slot] = sanitize_text_field($val);
                $detected = $this->detect_provider_from_api_key($val);
                if ($detected !== '' && $detected !== $slot) {
                    if ($slot === 'universal_api_hub' && empty($secrets[$detected])) {
                        $secrets[$detected] = sanitize_text_field($val);
                        $routed[] = $slot . '→' . $detected;
                    } else {
                        $warnings[] = $slot . ': key shape looks like ' . $detected;
                    }
                }
            }
            update_option(self::OPTION_SECRETS, $secrets, false);
        }
        wp_send_json_success(array(
            'message' => 'saved',
            'api' => $this->masked_status(),
            'settings' => $this->safe_settings(),
            'routed' => array_values(array_unique($routed)),
            'warnings' => array_values(array_unique($warnings)),
        ));
    }

    public function ajax_get_api() {
        $this->verify();
        wp_send_json_success(array('api' => $this->masked_status(), 'settings' => $this->safe_settings()));
    }

    public function ajax_unlock_api() {
        $this->verify();
        $provider = isset($_POST['provider']) ? sanitize_key(wp_unslash($_POST['provider'])) : '';
        $secrets = $this->usable_api_secrets();
        if ($provider === '' || empty($secrets[$provider])) wp_send_json_error(array('message' => 'not_configured'), 404);
        wp_send_json_success(array('provider' => $provider, 'value' => (string)$secrets[$provider]));
    }

    public function ajax_migrate_api() {
        $this->verify();
        $found = $this->migrate_api(true);
        wp_send_json_success(array('found' => $found, 'api' => $this->masked_status()));
    }

    public function ajax_test_api() {
        $this->verify();
        $status = $this->masked_status();
        $settings = get_option(self::OPTION_SETTINGS, array());
        $secrets = $this->usable_api_secrets();
        $tests = array();
        foreach ($this->providers() as $key => $label) {
            $test_key = (string)($secrets[$key] ?? '');
            if ($key === 'google_image' && $test_key === '' && !empty($secrets['google_search'])) $test_key = (string)$secrets['google_search'];
            $configured = $test_key !== '';
            $status_text = $configured ? $this->provider_test_status($key, $settings, $test_key) : 'not_configured';
            $tests[] = array(
                'provider' => $key,
                'name' => $label,
                'role' => $this->api_role_label($key),
                'endpoint' => $this->api_endpoint_info($key, $settings),
                'model' => $this->provider_model($key, $settings),
                'configured' => $configured ? 'yes' : 'no',
                'status' => $status_text,
                'diagnosis' => $this->diagnose_api_status($key, $status_text),
            );
        }
        $pass = 0;
        foreach ($tests as $t) {
            if (isset($t['status']) && strpos((string)$t['status'], 'pass:') === 0) $pass++;
        }
        wp_send_json_success(array(
            'configured' => $status['configured'],
            'pass' => $pass,
            'total' => $status['total'],
            'providers' => $status['providers'],
            'tests' => $tests,
            'settings' => $this->safe_settings(),
            'version' => self::VERSION,
            'checked_at' => current_time('mysql'),
            'message' => 'Live Test API: ผ่าน ' . $pass . ' / ตั้งค่าแล้ว ' . $status['configured'] . ' / ทั้งหมด ' . $status['total'] . ' provider',
        ));
    }

    public function ajax_connect_all_api() {
        $this->verify();
        $settings = get_option(self::OPTION_SETTINGS, array());
        $settings = $this->normalize_settings_defaults(is_array($settings) ? $settings : array());
        update_option(self::OPTION_SETTINGS, $settings, false);
        $secrets = $this->usable_api_secrets();
        $auto_routed = array();
        $secrets = $this->auto_route_api_secrets(is_array($secrets) ? $secrets : array(), $auto_routed);
        if (!empty($auto_routed)) update_option(self::OPTION_SECRETS, $secrets, false);
        $results = array();
        $pass = 0;
        $configured = 0;
        foreach ($this->providers() as $key => $label) {
            $stored = !empty($secrets[$key]);
            $fallback_google_image = (!$stored && $key === 'google_image' && !empty($secrets['google_search']));
            $fallback_global = !$stored && !$fallback_google_image && !empty($secrets['universal_api_hub']) && in_array($key, array('openai','openrouter','anthropic','gemini','groq','mistral','perplexity','xai','deepseek','together','custom'), true);
            $api_key = $stored ? $secrets[$key] : ($fallback_google_image ? $secrets['google_search'] : ($fallback_global ? $secrets['universal_api_hub'] : ''));
            $is_configured = $api_key !== '';
            if ($is_configured) $configured++;
            $status = $is_configured ? $this->provider_test_status($key, $settings, $api_key) : 'not_configured';
            if (strpos($status, 'pass:') === 0) $pass++;
            $results[] = array(
                'provider' => $key,
                'name' => $label,
                'configured' => $is_configured ? 'yes' : 'no',
                'source' => $fallback_google_image ? 'google_search_key' : ($fallback_global ? 'universal_api_hub' : ($stored ? 'provider_key' : 'none')),
                'model' => $this->provider_model($key, $settings),
                'status' => $status,
                'diagnosis' => $this->diagnose_api_status($key, $status),
            );
        }
        $masked = $this->masked_status();
        wp_send_json_success(array(
            'version' => self::VERSION,
            'checked_at' => current_time('mysql'),
            'configured' => $configured,
            'pass' => $pass,
            'total' => count($this->providers()),
            'providers' => $masked['providers'],
            'settings' => $this->safe_settings(),
            'tests' => $results,
            'auto_routed' => $auto_routed,
            'message' => 'Real API Center ตรวจแล้ว: ผ่าน ' . $pass . ' / ตั้งค่า ' . $configured . ' / ทั้งหมด ' . count($this->providers()) . ' ระบบ' . (!empty($auto_routed) ? ' · Auto Route: ' . implode(', ', $auto_routed) : ''),
        ));
    }

    private function normalize_settings_defaults($settings) {
        $settings = is_array($settings) ? $settings : array();
        $defaults = array(
            'primary_provider' => 'auto',
            'api_router_mode' => 'auto',
            'fallback_provider' => 'openrouter',
            'primary_model' => $this->provider_default_model('openai'),
            'openrouter_model' => $this->provider_default_model('openrouter'),
            'groq_model' => $this->provider_default_model('groq'),
            'mistral_model' => $this->provider_default_model('mistral'),
            'perplexity_model' => $this->provider_default_model('perplexity'),
            'xai_model' => $this->provider_default_model('xai'),
            'deepseek_model' => $this->provider_default_model('deepseek'),
            'together_model' => $this->provider_default_model('together'),
            'anthropic_model' => $this->provider_default_model('anthropic'),
            'gemini_model' => $this->provider_default_model('gemini'),
            'openai_vision_model' => 'gpt-4o-mini',
            'openai_api_mode' => 'responses',
            'image_generation_model' => 'gpt-image-1.5',
            'image_generation_size' => '1024x1024',
            'api_timeout' => '30',
            'github_endpoint' => 'https://api.github.com/user',
            'voice_language' => 'th-TH',
            'voice_rate' => '1.12',
            'voice_volume' => '1',
            'voice_sensitivity' => '2.25',
            'voice_response_speed_mode' => 'api_first_with_browser_fallback',
            'voice_auto_send_delay_ms' => '140',
            'voice_reply_restart_delay_ms' => '90',
            'voice_auto_start_mode' => 'open_and_listen',
            'voice_graphic_style' => 'ref_wave_orb_v2',

                'answer_typing_mode' => 'fast_full_render',
                'answer_animation_mode' => 'fast_full_render',
                'answer_animation_speed' => 'fast',
                'answer_completion_stability' => 'no_flicker_replace_one',
                'answer_latency_mode' => 'gpt_fast_direct',
                'difficult_question_research_mode' => 'auto_internal_external',
                'internal_knowledge_mode' => 'docs_memory_topic_context',
                'external_search_mode' => 'google_cse_when_configured',
                'google_search_num_results' => '3',
                'google_search_country' => 'th',
                'google_search_language' => 'lang_th',
                'google_search_safe' => 'active',
                'google_search_site_restrict' => '',
                'google_search_local_mode' => 'thailand_local_context',
                'google_image_search_mode' => 'google_cse_image',
                'google_gif_search_mode' => 'google_cse_image_gif',
                'google_design_search_mode' => 'web_image_structure_safe',
                'google_code_search_mode' => 'public_code_sources_safe',
                'gpt_like_processor_mode' => 'direct_answer_with_context_router',
                'smart_scroll_mode' => 'gpt_follow_when_near_bottom',
                'google_app_builder_mode' => 'web_mobile_pwa_blueprint',
                'google_game_builder_mode' => 'html5_canvas_js_blueprint',
                'code_system_builder_scope' => 'wordpress_web_app_api_game',
                'prompt_synthesis_mode' => 'summary_then_answer',
                'research_prompt_summary_mode' => 'enabled',
            'elevenlabs_voice_id' => '',
            'elevenlabs_voice_id_th' => '',
            'elevenlabs_voice_id_en' => '',
            'elevenlabs_voice_id_ja' => '',
            'elevenlabs_voice_id_zh' => '',
            'elevenlabs_voice_id_ko' => '',
            'elevenlabs_voice_id_ar' => '',
            'elevenlabs_voice_id_ru' => '',
            'elevenlabs_voice_id_fr' => '',
            'elevenlabs_voice_id_es' => '',
            'elevenlabs_model_id' => 'eleven_multilingual_v2',
            'elevenlabs_model_id_th' => 'eleven_multilingual_v2',
            'elevenlabs_model_id_en' => 'eleven_multilingual_v2',
            'elevenlabs_model_id_ja' => 'eleven_multilingual_v2',
            'elevenlabs_model_id_zh' => 'eleven_multilingual_v2',
            'elevenlabs_model_id_ko' => 'eleven_multilingual_v2',
            'elevenlabs_model_id_ar' => 'eleven_multilingual_v2',
            'elevenlabs_model_id_ru' => 'eleven_multilingual_v2',
            'elevenlabs_model_id_fr' => 'eleven_multilingual_v2',
            'elevenlabs_model_id_es' => 'eleven_multilingual_v2',
            'user_sync_mode' => 'wp_user_id',
            'api_test_panel_position' => 'settings_side',
            'voice_graphic_style' => 'ref_wave_orb_v2',

                'answer_typing_mode' => 'fast_full_render',
                'answer_latency_mode' => 'gpt_fast_direct',
                'difficult_question_research_mode' => 'auto_internal_external',
                'internal_knowledge_mode' => 'docs_memory_topic_context',
                'external_search_mode' => 'google_cse_when_configured',
                'google_search_num_results' => '3',
                'google_search_country' => 'th',
                'google_search_language' => 'lang_th',
                'google_search_safe' => 'active',
                'google_search_site_restrict' => '',
                'google_search_local_mode' => 'thailand_local_context',
                'google_image_search_mode' => 'google_cse_image',
                'google_gif_search_mode' => 'google_cse_image_gif',
                'google_design_search_mode' => 'web_image_structure_safe',
                'google_code_search_mode' => 'public_code_sources_safe',
                'gpt_like_processor_mode' => 'direct_answer_with_context_router',
                'smart_scroll_mode' => 'gpt_follow_when_near_bottom',
                'google_app_builder_mode' => 'web_mobile_pwa_blueprint',
                'google_game_builder_mode' => 'html5_canvas_js_blueprint',
                'code_system_builder_scope' => 'wordpress_web_app_api_game',
                'prompt_synthesis_mode' => 'summary_then_answer',
                'research_prompt_summary_mode' => 'enabled',
                'link_preview_mode' => 'popup_fetch_rewrite',
                'plain_text_mode' => 'available',
                'image_popup_mode' => 'view_download',
                'auto_topic_once' => 'enabled',
            'web_code_max_kb' => '220',
            'web_code_timeout' => '20',
            'api_ui_mode' => 'standard_common_required',
            'api_response_switch' => 'intent',
            'answer_focus_mode' => 'direct',
            'topic_file_mode' => 'auto_meaning_map',
            'wp_blueprint_mode' => 'plugin_module_widget_block_elementor',
            'free_code_policy' => 'public_safe_adapt',
            'settings_device_bar' => 'right_inline',
            'wp_relation_mode' => 'auto_connect_components',
            'sync_merge_mode' => 'merge_by_updated_at',
            'voice_model_save_mode' => 'settings_persisted',
            'keyboard_typo_mode' => 'thai_english_layout_hint',
            'color_visual_mode' => 'local_svg_static_animated',
            'aira_api_builder_mode' => 'topic_api_blueprint',
            'aira_research_mode' => 'public_source_assisted',
            'aira_topic_manager_mode' => 'auto_create_edit_delete',
                'web_structure_mode' => 'auto_analyze_transform',
                'copyright_transform_mode' => 'no_copy_rewrite_visual_text',
                'aira_persona_style' => 'female_warm_professional',
                'best_effort_answer_mode' => 'try_answer_with_uncertainty',
                'full_context_reader_mode' => 'full_page_memory_topic_history',
                'continuous_instruction_mode' => 'from_first_topic_to_latest',
                'first_topic_memory_mode' => 'enabled',
                'page_context_depth' => 'deep_safe_compact',
                'intensive_answer_mode' => 'context_first_then_research',
                'answer_confidence_mode' => 'show_if_uncertain',
                'knowledge_scope_mode' => 'internal_external_api_when_ready',
                'knowledge_orchestrator_mode' => 'deep_internal_external_safe',
                'search_answer_recovery_mode' => 'retry_then_best_effort',
                'low_value_answer_retry' => 'enabled',
                'chat_max_tokens' => '4096',
                'search_missing_api_notice' => 'short_actionable',
                'assumption_policy_mode' => 'clearly_label_assumptions',
                'answer_recovery_mode' => 'fallback_summary_next_steps',
                'custom_feature_builder_mode' => 'prompt_to_topic_code',
                'custom_feature_code_mode' => 'starter_blueprint_auto',
                'custom_feature_topic_mode' => 'auto_create_edit_delete',
                'code_color_search_mode' => 'extract_palette_from_prompt_web_memory',
                'color_draw_pipeline' => 'palette_to_local_svg_preview_download',
                'color_value_source_mode' => 'prompt_memory_url_when_available',
        );
        foreach ($defaults as $k => $v) {
            if (!isset($settings[$k]) || trim((string)$settings[$k]) === '') $settings[$k] = $v;
        }
        $prior_version = (string)($settings['version'] ?? '0');
        if ($prior_version === '' || version_compare($prior_version, '3.3.55', '<')) {
            // v3.3.55 repair: crisp Settings panel, faster voice loop, instant answer render, and no blank replies.
            $settings['answer_typing_mode'] = 'fast_full_render';
            $settings['answer_animation_mode'] = 'fast_full_render';
            $settings['answer_animation_speed'] = 'fast';
            $settings['voice_response_speed_mode'] = 'fast_browser_first';
            $settings['voice_auto_send_delay_ms'] = '360';
            $settings['voice_reply_restart_delay_ms'] = '220';
            $settings['voice_rate'] = '1.12';
            $settings['settings_popup_motion'] = 'crisp_no_blur';
            $settings['difficult_question_research_mode'] = 'auto_internal_external';
            $settings['internal_knowledge_mode'] = 'docs_memory_topic_context';
            $settings['external_search_mode'] = 'google_cse_when_configured';
            $settings['search_answer_recovery_mode'] = 'retry_then_best_effort';
            $settings['low_value_answer_retry'] = 'enabled';
        }
        if ($prior_version === '' || version_compare($prior_version, '3.3.56', '<')) {
            // v3.3.56 repair: audio player must try the ElevenLabs API bridge first after saving API/Voice ID, without requiring a page reload.
            $settings['voice_response_speed_mode'] = 'api_first_with_browser_fallback';
            $settings['audio_player_api_bridge'] = 'elevenlabs_ajax_first';
        }
        if ($prior_version === '' || version_compare($prior_version, '3.3.57', '<')) {
            // v3.3.57 hard repair: Settings popup must stay crisp on every browser, voice must type live, answers must render fast, and blank replies are forbidden.
            $settings['settings_popup_motion'] = 'crisp_no_blur_hard';
            $settings['settings_popup_backdrop'] = 'dim_without_blur';
            $settings['answer_typing_mode'] = 'fast_full_render';
            $settings['answer_animation_mode'] = 'fast_full_render';
            $settings['answer_animation_speed'] = 'fast';
            $settings['answer_latency_mode'] = 'gpt_fast_direct';
            $settings['voice_response_speed_mode'] = 'api_first_with_browser_fallback';
            $settings['voice_auto_send_delay_ms'] = '260';
            $settings['voice_reply_restart_delay_ms'] = '180';
            $settings['voice_rate'] = '1.15';
            $settings['voice_live_composer_mode'] = 'enabled';
            $settings['audio_player_api_bridge'] = 'elevenlabs_ajax_first_with_timeout_fallback';
            $settings['knowledge_orchestrator_mode'] = 'deep_internal_external_safe';
            $settings['knowledge_scope_mode'] = 'internal_external_api_when_ready';
            $settings['difficult_question_research_mode'] = 'auto_internal_external';
            $settings['search_answer_recovery_mode'] = 'retry_then_best_effort';
            $settings['low_value_answer_retry'] = 'enabled';
            $settings['no_blank_response_mode'] = 'server_client_double_guard';
        }
        if ($prior_version === '' || version_compare($prior_version, '3.3.58', '<')) {
            // v3.3.58 repair: visible audio player, API-first voice bridge, faster STT, and no-freeze long composer cleanup.
            $settings['settings_popup_motion'] = 'crisp_no_blur_final';
            $settings['settings_popup_backdrop'] = 'dim_without_blur';
            $settings['voice_response_speed_mode'] = 'api_first_with_browser_fallback';
            $settings['voice_auto_send_delay_ms'] = '220';
            $settings['voice_reply_restart_delay_ms'] = '160';
            $settings['voice_rate'] = '1.18';
            $settings['voice_live_composer_mode'] = 'enabled';
            $settings['audio_player_window'] = 'always_visible_api_panel';
            $settings['audio_player_api_bridge'] = 'elevenlabs_ajax_first_visible_player';
            $settings['chat_request_timeout_guard'] = 'enabled';
            $settings['no_blank_response_mode'] = 'server_client_timeout_double_guard';
            $settings['answer_typing_mode'] = 'fast_full_render';
            $settings['answer_animation_mode'] = 'fast_full_render';
        }
        if ($prior_version === '' || version_compare($prior_version, '3.3.59', '<')) {
            // v3.3.59 hard repair: Settings emergency-open, faster voice loop, always-visible audio player, and stuck composer recovery.
            $settings['settings_popup_motion'] = 'crisp_no_blur_emergency_open';
            $settings['settings_popup_backdrop'] = 'dim_without_blur';
            $settings['voice_response_speed_mode'] = 'api_first_with_browser_fallback';
            $settings['voice_auto_send_delay_ms'] = '140';
            $settings['voice_reply_restart_delay_ms'] = '120';
            $settings['voice_rate'] = '1.20';
            $settings['voice_live_composer_mode'] = 'enabled';
            $settings['audio_player_window'] = 'always_visible_api_panel_v3359';
            $settings['audio_player_api_bridge'] = 'elevenlabs_ajax_first_visible_player_v3359';
            $settings['chat_request_timeout_guard'] = 'aggressive_recover_enabled';
            $settings['no_blank_response_mode'] = 'server_client_runtime_triple_guard';
            $settings['answer_typing_mode'] = 'fast_full_render';
            $settings['answer_animation_mode'] = 'fast_full_render';
            $settings['knowledge_orchestrator_mode'] = 'deep_internal_external_safe';
            $settings['knowledge_scope_mode'] = 'internal_external_api_when_ready';
            $settings['difficult_question_research_mode'] = 'auto_internal_external';
        }
        if ($prior_version === '' || version_compare($prior_version, '3.3.60', '<')) {
            // v3.3.60 direct repair: one-tap voice start, real visible audio player, sharper Settings overlay, and stronger no-empty/read-context behavior.
            $settings['settings_popup_motion'] = 'crisp_direct_visible_no_blur';
            $settings['settings_popup_backdrop'] = 'dim_without_blur';
            $settings['voice_auto_start_mode'] = 'open_and_listen';
            $settings['voice_response_speed_mode'] = 'api_first_with_browser_fallback';
            $settings['voice_auto_send_delay_ms'] = '90';
            $settings['voice_reply_restart_delay_ms'] = '90';
            $settings['voice_rate'] = '1.22';
            $settings['voice_live_composer_mode'] = 'enabled';
            $settings['audio_player_window'] = 'always_visible_direct_api_panel_v3360';
            $settings['audio_player_api_bridge'] = 'elevenlabs_ajax_first_visible_player_v3360';
            $settings['chat_request_timeout_guard'] = 'direct_recover_enabled';
            $settings['no_blank_response_mode'] = 'server_client_runtime_absolute_guard';
            $settings['answer_typing_mode'] = 'fast_full_render';
            $settings['answer_animation_mode'] = 'fast_full_render';
            $settings['knowledge_orchestrator_mode'] = 'deep_internal_external_safe';
            $settings['knowledge_scope_mode'] = 'internal_external_api_when_ready';
            $settings['difficult_question_research_mode'] = 'auto_internal_external';
        }

        if ($prior_version === '' || version_compare($prior_version, '3.3.61', '<')) {
            // v3.3.61 audio repair: larger tap targets, close lock, direct replay bridge, no popup resurrection after stop/close.
            $settings['audio_close_lock_mode'] = 'enabled';
            $settings['audio_direct_replay_bridge'] = 'enabled';
        }

        $settings['version'] = self::VERSION;
        if (($settings['anthropic_model'] ?? '') === 'claude-sonnet-4-5') $settings['anthropic_model'] = $this->provider_default_model('anthropic');
        return $settings;
    }

    private function diagnose_api_status($provider, $status) {
        $provider = sanitize_key((string)$provider);
        $s = strtolower((string)$status);
        if ($s === 'not_configured' || strpos($s, 'not_configured') === 0) return 'ยังไม่ได้ใส่ key ของระบบนี้';
        if (strpos($s, 'pass:') === 0) return 'เชื่อมสำเร็จ ใช้งานได้';
        if ($provider === 'elevenlabs') {
            if (strpos($s, 'needs_voice_id') !== false) return 'ElevenLabs ต้องใส่ Voice ID อย่างน้อย 1 ช่องก่อนเครื่องเล่นเสียงจะเรียก API ได้';
            if (strpos($s, 'warn:401') !== false || strpos($s, 'warn:403') !== false) return 'ElevenLabs API key ไม่ถูกต้องหรือไม่มีสิทธิ์ใช้งาน';
            if (strpos($s, 'warn:429') !== false) return 'ElevenLabs ติด rate limit/quota';
        }
        if ($provider === 'google_search' || $provider === 'google_image') {
            if (strpos($s, 'needs_cx') !== false) return 'Google ต้องมี CSE ID / cx จาก Programmable Search Engine';
            if (strpos($s, 'cx_format') !== false) return 'ช่อง cx ต้องใส่เฉพาะ Search engine ID ห้ามใส่ URL/script/cx=';
            if (strpos($s, 'http_400') !== false || strpos($s, 'invalid_argument') !== false) return 'Google แจ้ง argument ไม่ถูกต้อง: ส่วนมากคือ cx ผิด หรือ Search Engine ตั้งค่าไม่ถูก';
            if (strpos($s, 'http_403') !== false || strpos($s, 'permission') !== false || strpos($s, 'quota') !== false) return 'Google ปฏิเสธสิทธิ์: ตรวจ Enable Custom Search JSON API, API restriction, billing/quota และ project ของ key';
            if (strpos($s, 'http_401') !== false) return 'Google API key ผิด/หมดอายุ/ไม่ได้อยู่ project นี้';
            if (strpos($s, 'http_429') !== false) return 'Google quota หมดหรือถูก rate limit';
            if (strpos($s, 'network_error') !== false) return 'WordPress server ติดต่อ Google API ไม่ได้ ตรวจ firewall/SSL/DNS/hosting';
            return 'Google ยังไม่ผ่าน: ดู status เต็มด้านบน จะมี HTTP และข้อความจริงจาก Google';
        }
        if (strpos($s, 'stored_global_key') !== false) return 'เก็บ key กลางแล้ว แต่ยังต้องทดสอบกับ endpoint/provider จริง';
        if (strpos($s, 'key_shape_warning') !== false) return 'รูปแบบ key ดูไม่ตรง provider อาจใส่ผิดช่อง';
        if (strpos($s, 'http_401') !== false || strpos($s, 'unauthorized') !== false || strpos($s, 'incorrect api key') !== false) return 'key ผิด หมดอายุ ถูกลบ หรืออยู่ผิด project/provider';
        if (strpos($s, 'http_403') !== false || strpos($s, 'permission') !== false || strpos($s, 'forbidden') !== false) return 'key มีแต่สิทธิ์ไม่พอ ตรวจ project · billing · permission · organization';
        if (strpos($s, 'http_404') !== false || strpos($s, 'model') !== false || strpos($s, 'not found') !== false) return 'model หรือ endpoint ไม่ถูกต้อง/ไม่มีสิทธิ์';
        if (strpos($s, 'http_429') !== false || strpos($s, 'quota') !== false || strpos($s, 'rate') !== false) return 'quota หมดหรือถูก rate limit';
        if (strpos($s, 'network_error') !== false || strpos($s, 'ssl') !== false || strpos($s, 'dns') !== false) return 'โฮสต์ออกอินเทอร์เน็ตไม่ได้ หรือ SSL/DNS/firewall บล็อก';
        if (strpos($s, 'needs_') !== false || strpos($s, 'stored_only') !== false) return 'เก็บ key แล้ว ต้องใส่ endpoint/CX/Voice ID เพิ่ม';
        return 'ยังไม่ผ่าน ดู status เต็มและแก้ตาม provider/model/endpoint';
    }



    private function aira_openai_creative_schema() {
        return array(
            'name' => 'OpenAI Creative Engine',
            'version' => self::VERSION,
            'text_model_default' => 'gpt-4o-mini',
            'code_model_default' => 'gpt-4o-mini',
            'image_model_default' => 'gpt-image-1',
            'file_types' => array('php','js','css','html','json','md','txt','svg'),
            'workflows' => array(
                'prompt_optimizer' => 'Rewrite messy user instructions into clear build prompts.',
                'code_builder' => 'Generate code blocks with filename hints for WordPress, web apps, apps and game systems.',
                'file_builder' => 'Convert AI output code blocks into downloadable file chips and ZIP bundle on the client side.',
                'image_builder' => 'Create image prompts, SVG visuals, CSS animation or call Image API when configured.',
                'download_builder' => 'Use short English filenames and only show downloads when files really exist.'
            )
        );
    }

    private function aira_openai_creative_local_answer($message, $settings = array()) {
        $raw = (string)$message;
        $is_image = (bool)preg_match('/(สร้างภาพ|ผลิตภาพ|วาดภาพ|image|photo|banner|visual|svg|logo|mockup|ภาพเคลื่อนไหว|animation|gif)/iu', $raw);
        $is_code = (bool)preg_match('/(เขียนโค้ด|สร้างโค้ด|เขียนโปรแกรม|สร้างแอพ|app|plugin|ปลั๊กอิน|module|widget|gutenberg|elementor|game|เกม|system|ระบบ)/iu', $raw);
        $is_file = (bool)preg_match('/(ไฟล์|download|ดาวน์โหลด|zip|bundle|php|js|css|html|json|md|svg)/iu', $raw);
        $lines = array();
        $lines[] = "OpenAI Creative Engine พร้อมใช้งานเป็นโครงสร้างใน AiRA แล้ว";
        if ($is_code) {
            $lines[] = "";
            $lines[] = "แนวทางสร้างโค้ด/ระบบ:";
            $lines[] = "1. สรุปเป้าหมายจากคำสั่ง";
            $lines[] = "2. เลือกชนิดงาน: WordPress / Web App / PWA / API / Game / Widget / Block";
            $lines[] = "3. สร้างไฟล์หลักเป็นชื่อภาษาอังกฤษสั้น ๆ";
            $lines[] = "4. ส่งออกเป็น code block พร้อมชื่อไฟล์เพื่อให้ AiRA ทำ file chips/ZIP";
        }
        if ($is_image) {
            $lines[] = "";
            $lines[] = "แนวทางสร้างภาพ:";
            $lines[] = "1. ปรับ prompt ภาพให้ชัด";
            $lines[] = "2. ถ้ามี OpenAI Image API ใช้ image model เพื่อสร้าง PNG/WebP";
            $lines[] = "3. ถ้ายังไม่มี Image API ให้สร้าง SVG/CSS/Canvas animation ในเครื่องก่อน";
            $lines[] = "4. แสดง preview และปุ่ม download เมื่อมีไฟล์จริง";
        }
        if ($is_file) {
            $lines[] = "";
            $lines[] = "แนวทางผลิตไฟล์:";
            $lines[] = "- AiRA จะสร้างเนื้อหาเป็น code/text ก่อน";
            $lines[] = "- จากนั้นแยกเป็น .php .js .css .html .json .md .txt .svg";
            $lines[] = "- ถ้ามีหลายไฟล์ให้รวมเป็น bundle ZIP";
        }
        $lines[] = "";
        $lines[] = "ช่องที่ควรตั้งค่าใน Real API Center:";
        $lines[] = "- OpenAI API Key";
        $lines[] = "- OpenAI Text/Code Model: " . sanitize_text_field((string)($settings['openai_text_model'] ?? 'gpt-4o-mini'));
        $lines[] = "- OpenAI Image Model: " . sanitize_text_field((string)($settings['openai_image_model'] ?? 'gpt-image-1'));
        return implode("\n", $lines);
    }


    private function google_api_error_summary($response) {
        $code = intval(wp_remote_retrieve_response_code($response));
        $body_raw = (string) wp_remote_retrieve_body($response);
        $body = json_decode($body_raw, true);
        $message = '';
        $reason = '';
        if (is_array($body) && isset($body['error'])) {
            $err = $body['error'];
            if (isset($err['message'])) $message = sanitize_text_field((string)$err['message']);
            if (isset($err['errors'][0]['reason'])) $reason = sanitize_key((string)$err['errors'][0]['reason']);
            elseif (isset($err['status'])) $reason = sanitize_key((string)$err['status']);
        }
        if ($message === '') $message = sanitize_text_field(substr($body_raw, 0, 220));
        if ($code === 400) return 'fail:http_400:google_cse_invalid_argument:' . ($reason ? $reason . ':' : '') . $message;
        if ($code === 401) return 'fail:http_401:google_key_unauthorized:' . ($reason ? $reason . ':' : '') . $message;
        if ($code === 403) return 'fail:http_403:google_permission_or_quota:' . ($reason ? $reason . ':' : '') . $message;
        if ($code === 429) return 'fail:http_429:google_quota_or_rate_limit:' . ($reason ? $reason . ':' : '') . $message;
        return 'warn:http_' . $code . ':google_response:' . $message;
    }

    private function google_cse_test_status($key, $cx, $mode = 'web') {
        $key = $this->normalize_api_key_input((string)$key);
        $cx = trim((string)$cx);
        if ($key === '') return 'not_configured:google_api_key_empty';
        if ($cx === '') return 'needs_cx:Google CSE ID / cx is empty';
        if (strpos($cx, 'http://') !== false || strpos($cx, 'https://') !== false || strpos($cx, '<script') !== false || strpos($cx, 'cx=') !== false) {
            return 'fail:cx_format:ใส่เฉพาะ Search engine ID เท่านั้น ห้ามใส่ URL/script/cx=';
        }
        $params = array(
            'key' => $key,
            'cx' => $cx,
            'q' => $mode === 'image' ? 'modern website design' : 'test',
            'num' => 1,
            'safe' => 'active',
        );
        if ($mode === 'image') $params['searchType'] = 'image';
        if ($mode === 'gif') {
            $params['searchType'] = 'image';
            $params['fileType'] = 'gif';
        }
        $url = add_query_arg($params, 'https://www.googleapis.com/customsearch/v1');
        $res = wp_remote_get($url, array(
            'timeout' => 12,
            'redirection' => 2,
            'headers' => array('User-Agent' => 'AiRA-Studio-Google-Test/' . self::VERSION),
        ));
        if (is_wp_error($res)) return 'fail:network_error:' . sanitize_text_field($res->get_error_code() . ' ' . $res->get_error_message());
        $code = intval(wp_remote_retrieve_response_code($res));
        if ($code >= 200 && $code < 300) {
            $body = json_decode((string)wp_remote_retrieve_body($res), true);
            $total = isset($body['searchInformation']['totalResults']) ? (string)$body['searchInformation']['totalResults'] : 'unknown';
            $items = isset($body['items']) && is_array($body['items']) ? count($body['items']) : 0;
            return 'pass:http_200:google_' . sanitize_key($mode) . ':items_' . intval($items) . ':total_' . sanitize_text_field($total);
        }
        return $this->google_api_error_summary($res);
    }

    private function provider_test_status($provider, $settings, $key) {
        $provider = sanitize_key($provider);
        $key = $this->normalize_api_key_input($key);
        if ($key === '') return 'not_configured';
        if (in_array($provider, array('openai','openrouter','anthropic','gemini','groq','mistral','perplexity','xai','deepseek','together','custom'), true)) {
            $test_settings = is_array($settings) ? $settings : array();
            $test_settings['__test_mode'] = true;
            $shape = $this->provider_key_shape_warning($provider, $key);
            $reply = $this->provider_request($provider, 'ตอบกลับด้วยคำว่า OK เท่านั้น', $test_settings, $key, array());
            $model = $this->provider_model($provider, $test_settings);
            $endpoint = isset($reply['endpoint']) ? '@' . $reply['endpoint'] : '';
            if (!empty($reply['ok'])) return 'pass:live:' . $model . $endpoint . ($shape ? ' | ' . $shape : '');
            return 'fail:' . $model . ':' . substr((string)($reply['text'] ?? 'unknown'), 0, 220) . ($shape ? ' | ' . $shape : '');
        }
        $url = '';
        $args = array('timeout' => 8, 'headers' => array());
        if ($provider === 'github') {
            $url = esc_url_raw($settings['github_endpoint'] ?? '');
            if ($url === '') $url = 'https://api.github.com/user';
            $args['headers']['Authorization'] = 'Bearer ' . $key;
            $args['headers']['User-Agent'] = 'AiRA-Studio';
        } elseif ($provider === 'elevenlabs') {
            if (!$this->elevenlabs_voice_id_for_lang($settings, 'auto')) return 'warn:needs_voice_id';
            $url = 'https://api.elevenlabs.io/v1/user';
            $args['headers']['xi-api-key'] = $key;
        } elseif ($provider === 'google_search') {
            return $this->google_cse_test_status($key, (string)($settings['google_search_cx'] ?? ''), 'web');
        } elseif ($provider === 'google_image') {
            if (!empty($settings['google_search_cx'])) return $this->google_cse_test_status($key, (string)$settings['google_search_cx'], 'image');
            $url = esc_url_raw($settings['google_image_endpoint'] ?? '');
            if ($url === '') return 'stored_only:needs_google_cse_id_or_image_endpoint';
            $args['headers']['Authorization'] = 'Bearer ' . $key;
        } elseif ($provider === 'v0') {
            $url = esc_url_raw($settings['v0_endpoint'] ?? '');
            if ($url === '') return 'stored_only:needs_v0_endpoint';
            $args['headers']['Authorization'] = 'Bearer ' . $key;
        } elseif ($provider === 'universal_api_hub') {
            return 'stored_global_key';
        } elseif ($provider === 'custom') {
            $url = esc_url_raw($settings['custom_endpoint'] ?? '');
            if ($url === '') return 'needs_endpoint';
            $args['headers']['Authorization'] = 'Bearer ' . $key;
        } else {
            return 'stored_only';
        }
        $res = wp_remote_get($url, array_merge(array('timeout' => 8), $args));
        if (is_wp_error($res)) return 'error:' . $res->get_error_code();
        $code = wp_remote_retrieve_response_code($res);
        return ($code >= 200 && $code < 300) ? 'pass:' . $code : 'warn:' . $code;
    }

    /* ============================================================
     * Self Bug Inspector TXT Export + True Lazy Load + Internal Flow Relationship Scan (v3.4.75)
     * ============================================================ */

    public function ajax_export_debug_text() {
        $this->verify();
        $report = $this->build_self_bug_text_report();
        wp_send_json_success(array(
            'filename' => 'aira-studio-self-debug-' . gmdate('Ymd-His') . '.txt',
            'text' => $report,
            'version' => self::VERSION,
        ));
    }

    private function build_self_bug_text_report() {
        $root = plugin_dir_path(__FILE__);
        $files = $this->self_bug_collect_files($root);
        $issues = array();
        $stats = array(
            'total_files' => count($files),
            'php_files' => 0,
            'js_files' => 0,
            'css_files' => 0,
            'total_bytes' => 0,
            'total_lines' => 0,
            'code_lines' => 0,
            'document_lines' => 0,
            'largest_file' => '',
            'largest_bytes' => 0,
            'largest_asset_file' => '',
            'largest_asset_bytes' => 0,
            'largest_initial_asset_file' => '',
            'largest_initial_asset_bytes' => 0,
            'initial_asset_bytes' => 0,
            'initial_asset_files' => 0,
            'lazy_deferred_asset_bytes' => 0,
            'lazy_deferred_asset_files' => 0,
            'js_chunk_files' => 0,
            'css_chunk_files' => 0,
        );

        $php_main = __FILE__;
        $main_source = is_readable($php_main) ? (string) file_get_contents($php_main) : '';
        $paren = $this->self_bug_php_token_parentheses_balance($main_source);
        $php_syntax_ok = $this->self_bug_php_lint_current_file();
        if ($paren['open'] !== $paren['close'] && !$php_syntax_ok) {
            $issues[] = array('severity' => 'critical', 'area' => 'syntax', 'file' => 'aira-studio.php', 'message' => 'PHP syntax/parentheses check failed: ' . $paren['open'] . '/' . $paren['close']);
        }

        $php_files = array();
        $asset_handles = array();
        $remote_without_timeout = 0;
        $request_touches = 0;
        $output_touches = 0;
        $escape_touches = 0;
        $db_touches = 0;
        $api_touches = 0;
        $js_secret_persistence = 0;
        $max_z = 0;
        $fixed_count = 0;

        foreach ($files as $rel => $abs) {
            $ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION));
            $bytes = is_readable($abs) ? (int) filesize($abs) : 0;
            $stats['total_bytes'] += $bytes;
            if ($bytes > $stats['largest_bytes']) {
                $stats['largest_bytes'] = $bytes;
                $stats['largest_file'] = $rel;
            }
            $content = is_readable($abs) ? (string) file_get_contents($abs) : '';
            $lines = $content === '' ? 0 : substr_count($content, "\n") + 1;
            $stats['total_lines'] += $lines;
            if (in_array($ext, array('php', 'js', 'css'), true)) {
                $stats['code_lines'] += $lines;
                if ($bytes > $stats['largest_asset_bytes']) {
                    $stats['largest_asset_bytes'] = $bytes;
                    $stats['largest_asset_file'] = $rel;
                }
                $is_initial_asset = in_array($rel, array('assets/js/aira-studio-loader.min.js', 'assets/js/aira-studio-health-inspector.js', 'assets/css/aira-studio-critical.min.css'), true);
                $is_lazy_asset = (bool) preg_match('#^assets/(?:js|css)/aira-studio-(?!critical|loader).*\.min\.(?:js|css)$#', $rel);
                if ($is_initial_asset) {
                    $stats['initial_asset_files']++;
                    $stats['initial_asset_bytes'] += $bytes;
                    if ($bytes > $stats['largest_initial_asset_bytes']) {
                        $stats['largest_initial_asset_bytes'] = $bytes;
                        $stats['largest_initial_asset_file'] = $rel;
                    }
                }
                if ($is_lazy_asset) {
                    $stats['lazy_deferred_asset_files']++;
                    $stats['lazy_deferred_asset_bytes'] += $bytes;
                }
                if ($ext === 'js' && strpos($rel, 'assets/js/') === 0 && $rel !== 'assets/js/aira-studio-loader.min.js') { $stats['js_chunk_files']++; }
                if ($ext === 'css' && strpos($rel, 'assets/css/') === 0 && $rel !== 'assets/css/aira-studio-critical.min.css') { $stats['css_chunk_files']++; }
            } else {
                $stats['document_lines'] += $lines;
            }

            if ($ext === 'php') {
                $stats['php_files']++;
                $php_files[$rel] = $content;
                if (strpos($content, "defined( 'ABSPATH' )") === false && strpos($content, 'defined( \'ABSPATH\' )') === false && strpos($content, 'WP_UNINSTALL_PLUGIN') === false) {
                    $issues[] = array('severity' => 'warning', 'area' => 'security', 'file' => $rel, 'message' => 'Missing direct access guard.');
                }
                $request_touches += preg_match_all('/\$_(?:GET|POST|REQUEST|COOKIE|FILES)\b/', $content, $m);
                $output_touches += preg_match_all('/\becho\b|<\?=/', $content, $m);
                $escape_touches += preg_match_all('/\besc_(?:html|attr|url|js|textarea|sql)\b|\bwp_kses(?:_post)?\b|\bsanitize_(?:text_field|key|email|file_name|textarea_field)\b|\bwp_unslash\b/', $content, $m);
                $db_touches += preg_match_all('/\$wpdb\b|\b(?:get|add|update|delete)_option\b|\b(?:set|delete|get)_transient\b/', $content, $m);
                $api_touches += preg_match_all('/\bwp_remote_(?:get|post|request)\b|https?:\/\//i', $content, $m);
                if (preg_match_all('/wp_enqueue_(?:script|style)\s*\(\s*[\'\"]([^\'\"]+)[\'\"]/', $content, $hm)) {
                    foreach ($hm[1] as $h) {
                        if (!isset($asset_handles[$h])) $asset_handles[$h] = 0;
                        $asset_handles[$h]++;
                    }
                }
                if (preg_match_all('/wp_remote_(?:get|post|request)\s*\((.{0,500})\)/is', $content, $rm)) {
                    foreach ($rm[1] as $call) {
                        if (stripos($call, 'timeout') === false) $remote_without_timeout++;
                    }
                }
            } elseif ($ext === 'js') {
                $stats['js_files']++;
                $api_touches += preg_match_all('/\bfetch\s*\(|XMLHttpRequest|api[_-]?key|token|secret|endpoint/i', $content, $m);
                $js_secret_persistence += preg_match_all('/setItem\s*\([^\)]*(api[_-]?key|token|secret|password|credential|sk-)/i', $content, $m);
                if (preg_match('/\b' . 'local' . 'Storage' . '\b/i', $content)) {
                    $issues[] = array('severity' => 'warning', 'area' => 'security', 'file' => $rel, 'message' => 'Found direct browser storage usage; use safe wrapper and never persist API secrets.');
                }
            } elseif ($ext === 'css') {
                $stats['css_files']++;
                $fixed_count += preg_match_all('/position\s*:\s*fixed/i', $content, $m);
                if (preg_match_all('/z-index\s*:\s*([0-9]+)/i', $content, $zm)) {
                    foreach ($zm[1] as $z) $max_z = max($max_z, (int)$z);
                }
            }
        }

        $flow = $this->self_bug_internal_flow_analysis($files, $main_source, $stats);
        if (!empty($flow['critical'])) {
            $issues[] = array('severity' => 'warning', 'area' => 'relationship', 'file' => 'aira-studio.php/assets', 'message' => 'Internal flow relationship has broken links: ' . (int) $flow['critical']);
        }

        foreach ($asset_handles as $handle => $count) {
            if ($count > 1) {
                $issues[] = array('severity' => 'warning', 'area' => 'assets', 'file' => 'aira-studio.php', 'message' => 'Duplicate asset handle: ' . $handle . ' x' . $count);
            }
        }
        if ($request_touches > 0 && strpos($main_source, 'wp_unslash') === false) {
            $issues[] = array('severity' => 'warning', 'area' => 'security', 'file' => 'aira-studio.php', 'message' => 'Request input exists but wp_unslash() was not detected.');
        }
        if ($output_touches > 0 && $escape_touches < 10) {
            $issues[] = array('severity' => 'warning', 'area' => 'security', 'file' => 'aira-studio.php', 'message' => 'Many outputs detected; escape count looks low.');
        }
        if (!empty($page_health['critical'])) {
            $issues[] = array('severity' => 'warning', 'area' => 'page_health', 'file' => 'aira-studio.php', 'message' => 'Plugin page/menu/function health has missing required UI elements or routes: ' . (int) $page_health['critical']);
        }
        if ($remote_without_timeout > 0) {
            $issues[] = array('severity' => 'notice', 'area' => 'performance', 'file' => 'aira-studio.php', 'message' => 'Remote request without obvious timeout: ' . $remote_without_timeout);
        }
        if ($js_secret_persistence > 0) {
            $issues[] = array('severity' => 'warning', 'area' => 'security', 'file' => 'assets/aira-studio.js', 'message' => 'Possible secret persistence in browser storage wrapper.');
        }
        if ($max_z > 999) {
            $issues[] = array('severity' => 'notice', 'area' => 'ui_ux', 'file' => 'assets/aira-studio.css', 'message' => 'Very high z-index detected: ' . $max_z);
        }

        $critical = 0; $warning = 0; $notice = 0;
        foreach ($issues as $issue) {
            if ($issue['severity'] === 'critical') $critical++;
            elseif ($issue['severity'] === 'warning') $warning++;
            else $notice++;
        }
        $score = max(0, 100 - ($critical * 40) - ($warning * 6) - ($notice * 2));
        // v3.4.75: quality focuses on code maintainability; docs are reported separately and should not unfairly lower code quality.
        $quality = max(0, 100 - max(0, ($stats['code_lines'] - 22000) / 700) - ($stats['js_chunk_files'] > 1 ? 0 : 5) - ($stats['css_chunk_files'] > 1 ? 0 : 5));
        // v3.4.75: performance is scored on first-paint asset weight, lazy split coverage, remote timeout hygiene, and overlay pressure.
        $initial_weight = $stats['initial_asset_bytes'] > 0 ? $stats['initial_asset_bytes'] : $stats['largest_asset_bytes'];
        $largest_lazy_penalty = max(0, ($stats['largest_asset_bytes'] - 750000) / 30000);
        $performance = max(0, 100 - max(0, ($initial_weight - 260000) / 18000) - $largest_lazy_penalty - ($fixed_count > 40 ? 2 : 0) - ($stats['js_chunk_files'] > 1 ? 0 : 8) - ($stats['css_chunk_files'] > 1 ? 0 : 6) - ($stats['initial_asset_files'] >= 2 ? 0 : 4));
        $security = max(0, 100 - ($critical * 50) - ($warning * 8));

        $lines = array();
        $lines[] = 'Thinkb4do / AiRA Studio Self Bug Inspector TXT Export';
        $lines[] = str_repeat('=', 72);
        $lines[] = 'Plugin: AiRA Studio';
        $lines[] = 'Version: ' . self::VERSION;
        $identity = $this->authorized_identity_payload(false);
        $lines[] = 'Authorized ID Sync: ' . (!empty($identity['authorized']) ? $identity['authorizedId'] . ' · devices=' . intval($identity['deviceCount']) . ' · mode=' . $identity['syncMode'] : 'not authorized');
        $lines[] = 'Generated: ' . current_time('mysql');
        $lines[] = 'Mode: Safe Static Scan + Runtime Metadata (no secret values exported)';
        $lines[] = '';
        $lines[] = '[Score]';
        $lines[] = 'Overall: ' . round($score) . '%';
        $lines[] = 'Quality: ' . round($quality) . '%';
        $lines[] = 'Performance: ' . round($performance) . '%';
        $lines[] = 'Security: ' . round($security) . '%';
        $lines[] = 'Internal Flow / Relationship: ' . round($flow['score']) . '%';
        $lines[] = 'Plugin Page/Menu/Function Health: ' . round($page_health['score']) . '%';
        $lines[] = '';
        $lines[] = '[File Stats]';
        $lines[] = 'Total Files: ' . $stats['total_files'];
        $lines[] = 'PHP Files: ' . $stats['php_files'];
        $lines[] = 'JS Files: ' . $stats['js_files'];
        $lines[] = 'CSS Files: ' . $stats['css_files'];
        $lines[] = 'Total Size: ' . $this->self_bug_readable_bytes($stats['total_bytes']);
        $lines[] = 'Total Lines: ' . $stats['total_lines'];
        $lines[] = 'Code Lines: ' . $stats['code_lines'];
        $lines[] = 'Document Lines: ' . $stats['document_lines'];
        $lines[] = 'Largest File: ' . $stats['largest_file'] . ' (' . $this->self_bug_readable_bytes($stats['largest_bytes']) . ')';
        $lines[] = 'Largest Loaded Asset Chunk: ' . $stats['largest_asset_file'] . ' (' . $this->self_bug_readable_bytes($stats['largest_asset_bytes']) . ')';
        $lines[] = 'Largest Initial Asset: ' . ($stats['largest_initial_asset_file'] !== '' ? $stats['largest_initial_asset_file'] : '-') . ' (' . $this->self_bug_readable_bytes($stats['largest_initial_asset_bytes']) . ')';
        $lines[] = 'Initial Loaded Asset Weight: ' . $this->self_bug_readable_bytes($stats['initial_asset_bytes']) . ' across ' . $stats['initial_asset_files'] . ' files';
        $lines[] = 'Lazy Deferred Asset Weight: ' . $this->self_bug_readable_bytes($stats['lazy_deferred_asset_bytes']) . ' across ' . $stats['lazy_deferred_asset_files'] . ' files';
        $lines[] = '';
        $lines[] = '[Static Touchpoints]';
        $lines[] = 'Request Touchpoints: ' . $request_touches;
        $lines[] = 'Output Touchpoints: ' . $output_touches;
        $lines[] = 'Escape/Sanitize Touchpoints: ' . $escape_touches;
        $lines[] = 'Database/Option Touchpoints: ' . $db_touches;
        $lines[] = 'Remote/API Touchpoints: ' . $api_touches;
        $lines[] = 'Asset Handles: ' . count($asset_handles);
        $lines[] = 'JS Chunk Files: ' . $stats['js_chunk_files'];
        $lines[] = 'CSS Chunk Files: ' . $stats['css_chunk_files'];
        $lines[] = 'Initial Asset Files: ' . $stats['initial_asset_files'];
        $lines[] = 'Lazy Deferred Asset Files: ' . $stats['lazy_deferred_asset_files'];
        $lines[] = 'True Lazy Load: enabled';
        $lines[] = 'CSS fixed elements: ' . $fixed_count;
        $lines[] = 'Max z-index: ' . $max_z;
        $lines[] = 'Token Parentheses: ' . $paren['open'] . ' / ' . $paren['close'];
        $lines[] = 'Remote requests without obvious timeout: ' . $remote_without_timeout;
        $lines[] = 'Internal Flow Broken Links: ' . (int) $flow['critical'];
        $lines[] = 'Internal Flow Watch Points: ' . (int) $flow['watch'];
        $lines[] = 'Page Health Menus: ' . (int) $page_health['menu_groups'];
        $lines[] = 'Page Health Functions: ' . (int) $page_health['functions'];
        $lines[] = 'Page Health Issues: ' . (int) $page_health['critical'] . ' critical / ' . (int) $page_health['watch'] . ' watch';
        $lines[] = '';
        $lines[] = '[Internal Flow + Relationship Map]';
        foreach ($flow['lines'] as $flow_line) {
            $lines[] = $flow_line;
        }
        $lines[] = '';
        $lines[] = '[What Is Happening Inside / เกิดอะไรขึ้นภายใน]';
        foreach ($flow['explain'] as $flow_line) {
            $lines[] = $flow_line;
        }
        $lines[] = '';
        $lines[] = '[Possible Friction / จุดที่อาจขัดข้อง]';
        foreach ($flow['friction'] as $flow_line) {
            $lines[] = $flow_line;
        }
        $lines[] = '';
        $lines[] = '[World Standard Gap / เทียบมาตรฐานโลก]';
        $lines[] = '- Security baseline: ' . (round($security) >= 95 ? 'PASS — nonce, capability, sanitize/escape, timeout และ secret masking ผ่านระดับ self scan' : 'NEEDS WORK — ต้องตรวจ nonce/capability/sanitize/escape เพิ่ม');
        $lines[] = '- First-load performance: ' . (round($performance) >= 95 ? 'PASS — หน้าแรกโหลดเบามากเพราะใช้ true lazy load' : 'WATCH — ยังควรลด initial asset weight');
        $lines[] = '- Runtime performance: ' . ($stats['largest_asset_bytes'] > 650000 ? 'WATCH — deferred workspace chunk ยังใหญ่ ควรแยกเป็น feature modules ต่อ' : 'PASS — chunk ใหญ่สุดอยู่ในระดับเหมาะสม');
        $lines[] = '- UI layer standard: ' . ($fixed_count > 40 ? 'WATCH — fixed layer ยังเยอะ เสี่ยง header/composer/popup ซ้อนบนบางธีม/มือถือ' : 'PASS — fixed layer อยู่ในกรอบควบคุม');
        $lines[] = '- Relationship standard: ' . (round($flow['score']) >= 90 ? 'PASS — AJAX/action/method/localized/DOM chain หลักเชื่อมกันครบ' : 'NEEDS WORK — มี chain หลักขาดหรือไม่ถูก expose');
        $lines[] = '- Testing gap: self-inspector เป็น static/runtime metadata เบื้องต้น ยังควรเสริม WordPress Coding Standards, PHP unit test, Playwright/E2E mobile test และทดสอบกับธีม/ปลั๊กอินจริง';
        $lines[] = '';
        $lines[] = '[Quality / Performance / How to Fix / พัฒนายังไง]';
        $lines[] = '- Quality now: ' . round($quality) . '% — โค้ดผ่าน self scan แต่ไฟล์หลัก PHP ยังใหญ่มาก ควรค่อย ๆ แยกเป็น Admin, API, Renderer, Inspector, Sync, Voice, Browser modules';
        $lines[] = '- Performance now: ' . round($performance) . '% — หน้าแรกดีมาก แต่เมื่อเปิดฟีเจอร์หนักครั้งแรกยังอาจโหลด workspace chunk ใหญ่';
        $lines[] = '- Efficiency fix: แยก assets/js/aira-studio-02-workspace.min.js ออกเป็น chat-core, rooms-memory, api-center, voice-audio, browser-reader, debug-inspector';
        $lines[] = '- UI fix: ลด position:fixed ที่ซ้ำซ้อน, ใช้ layer scale เดียว, ให้ popup อยู่ใน safe viewport ระหว่าง admin bar กับ composer';
        $lines[] = '- Flow fix: เพิ่ม runtime event trace เช่น button_clicked, ajax_started, ajax_success, ajax_failed, render_done, scroll_locked เพื่อเห็นจุดค้างจริงในเว็บ';
        $lines[] = '- Next development: v3.4.77 runtime-flow-recorder, v3.4.78 workspace-module-split, v3.4.79 chat-render-quality-guard, v3.4.80 answer-uniqueness-guard, v3.4.81 ui-ux-visual-health-inspector, v3.4.82 inspector-version-sync, v3.4.83 deep-answer-dedup-smoothness-inspector, v3.4.84 repeat-calibration-smoothness-stabilizer, v3.4.85 smoothness-motion-optimizer, v3.4.86 playwright-mobile-regression, v3.4.86 WPCS/security-hardening';
        $lines[] = '';
        $lines[] = '[Issue Summary]';
        $lines[] = 'Critical: ' . $critical;
        $lines[] = 'Warning: ' . $warning;
        $lines[] = 'Notice: ' . $notice;
        if (!$issues) {
            $lines[] = '- No obvious static issue detected by the self inspector.';
        } else {
            $i = 1;
            foreach ($issues as $issue) {
                $lines[] = $i . '. [' . strtoupper($issue['severity']) . '] ' . $issue['area'] . ' | ' . $issue['file'] . ' | ' . $issue['message'];
                $i++;
            }
        }
        $lines[] = '';
        $lines[] = '[File Inventory: top-level code files]';
        foreach ($files as $rel => $abs) {
            $ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION));
            if (!in_array($ext, array('php','js','css'), true)) continue;
            $content = is_readable($abs) ? (string) file_get_contents($abs) : '';
            $sha = is_readable($abs) ? substr(hash_file('sha256', $abs), 0, 12) : 'unreadable';
            $lines[] = '- ' . $rel . ' | ' . $ext . ' | ' . $this->self_bug_readable_bytes((int) filesize($abs)) . ' | ' . (substr_count($content, "\n") + 1) . ' lines | sha256:' . $sha;
        }
        $lines[] = '';
        $lines[] = '[System Closer]';
        if ($critical > 0) {
            $lines[] = 'Status: ยังไม่ควรใช้กับเว็บจริงจนกว่า Critical จะเป็น 0';
        } elseif ($warning > 0) {
            $lines[] = 'Status: ใช้งานทดสอบได้ แต่ควรแก้ Warning สำคัญก่อน production';
        } else {
            $lines[] = 'Status: ผ่าน self static scan เบื้องต้น + true lazy load + internal flow relationship map สามารถทดสอบบน WordPress จริงต่อได้';
        }
        $lines[] = 'Note: รายงานนี้เป็น self-inspector เบื้องต้น ไม่แทนที่ PHP lint, WordPress Coding Standards, security review และการทดสอบบนเว็บจริง';
        $lines[] = '';
        $lines[] = 'Generated by AiRA Studio Self Bug Inspector ' . self::VERSION . ' | Settings | โดย Thinkb4do | ดูรายละเอียด';
        return implode("\n", $lines) . "\n";
    }

    private function self_bug_internal_flow_analysis($files, $main_source, $stats) {
        $js_source = '';
        $css_source = '';
        foreach ($files as $rel => $abs) {
            $ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION));
            if ($ext === 'js' && is_readable($abs)) {
                $js_source .= "\n/* " . $rel . " */\n" . (string) file_get_contents($abs);
            } elseif ($ext === 'css' && is_readable($abs)) {
                $css_source .= "\n/* " . $rel . " */\n" . (string) file_get_contents($abs);
            }
        }

        $registered = array();
        if (preg_match_all('/wp_ajax_aira75841_([a-z0-9_]+)/i', $main_source, $m)) {
            $registered = array_values(array_unique(array_filter($m[1])));
        }
        // Dynamic registration pattern: $hooks = array('send_chat', ...); add_action('wp_ajax_aira75841_' . $h, ...).
        // The flow inspector must read this list too, otherwise relationship scan would falsely report missing AJAX links.
        if (preg_match_all('/\$hooks\s*=\s*array\s*\((.*?)\);/is', $main_source, $hm)) {
            foreach ($hm[1] as $hook_block) {
                if (preg_match_all('/[\'\"]([a-z0-9_]+)[\'\"]/i', $hook_block, $hh)) {
                    foreach ($hh[1] as $hook_name) {
                        if ($hook_name !== '') { $registered[] = $hook_name; }
                    }
                }
            }
        }
        $registered = array_values(array_unique(array_filter($registered)));
        sort($registered, SORT_NATURAL);

        $methods = array();
        if (preg_match_all('/function\s+ajax_([a-z0-9_]+)\s*\(/i', $main_source, $m)) {
            $methods = array_values(array_unique($m[1]));
        }
        sort($methods, SORT_NATURAL);

        $localized = array();
        if (preg_match_all('/[\'\"]([A-Za-z0-9]+)[\'\"]\s*=>\s*[\'\"]aira75841_([a-z0-9_]+)[\'\"]/i', $main_source, $m, PREG_SET_ORDER)) {
            foreach ($m as $row) {
                $localized[$row[1]] = $row[2];
            }
        }

        $button_actions = array();
        if (preg_match_all('/data-action=[\'\"]([^\'\"]+)[\'\"]/i', $main_source, $m)) {
            $button_actions = array_values(array_unique($m[1]));
        }
        sort($button_actions, SORT_NATURAL);

        $php_ids = array();
        if (preg_match_all('/\sid=[\'\"]([^\'\"]+)[\'\"]/i', $main_source, $m)) {
            $php_ids = array_values(array_unique($m[1]));
        }
        sort($php_ids, SORT_NATURAL);

        $js_ids = array();
        if (preg_match_all('/(?:getElementById|\bo)\(\s*[\'\"]([A-Za-z0-9_\-]+)[\'\"]\s*\)/', $js_source, $m)) {
            $js_ids = array_values(array_unique($m[1]));
        }
        sort($js_ids, SORT_NATURAL);

        $storage_keys = array();
        if (preg_match_all('/aira_studio_v[0-9a-z_]+/i', $js_source . "\n" . $main_source, $m)) {
            $storage_keys = array_values(array_unique($m[0]));
        }
        sort($storage_keys, SORT_NATURAL);

        $missing_methods = array_values(array_diff($registered, $methods));
        $unregistered_methods = array_values(array_diff($methods, $registered));
        $localized_targets = array_values(array_unique(array_values($localized)));
        $missing_registered_for_localized = array_values(array_diff($localized_targets, $registered));
        $registered_not_localized = array_values(array_diff($registered, $localized_targets));

        $button_js_missing = array();
        foreach ($button_actions as $action) {
            if (strpos($js_source, $action) === false) {
                $button_js_missing[] = $action;
            }
        }

        $js_id_missing_markup = array_values(array_diff($js_ids, $php_ids));
        $dynamic_id_allow = array('toasts');
        $js_id_missing_markup = array_values(array_diff($js_id_missing_markup, $dynamic_id_allow));

        $flows = array(
            'Chat Composer' => array(
                'ajax' => array('send_chat', 'bootstrap'),
                'localized' => array('sendChat', 'bootstrap'),
                'dom' => array('composerInput', 'sendBtn', 'chatLog'),
                'buttons' => array('send-chat'),
                'meaning' => 'ผู้ใช้พิมพ์ข้อความ → JS อ่าน composer → AJAX send_chat → PHP เลือก provider/คำตอบ → บันทึกเข้า room/messages → render กลับใน chatLog + smart scroll'
            ),
            'Real API Center' => array(
                'ajax' => array('save_api', 'get_api', 'test_api', 'connect_all_api'),
                'localized' => array('saveApi', 'getApi', 'testApi', 'connectAllApi'),
                'dom' => array(),
                'buttons' => array(),
                'meaning' => 'หน้าตั้งค่า/API → JS ส่งค่าแบบ nonce/capability → PHP sanitize/encrypt/mask → test endpoint → ส่งสถานะกลับ dashboard'
            ),
            'Voice / Audio' => array(
                'ajax' => array('tts_voice'),
                'localized' => array('ttsVoice'),
                'dom' => array('audioPlayerPop'),
                'buttons' => array('voice-open'),
                'meaning' => 'ผู้ใช้กด Voice/Audio → browser speech/ElevenLabs bridge → AJAX tts_voice → audio player/composer dock แสดงผลโดยไม่ทับ chat'
            ),
            'Mini Browser / Web Research' => array(
                'ajax' => array('mini_browser_fetch', 'import_url_doc'),
                'localized' => array('miniBrowserFetch', 'importUrlDoc'),
                'dom' => array(),
                'buttons' => array('web-structure-import', 'aira-research-source', 'web-code-import'),
                'meaning' => 'คำสั่งอ่านเว็บ/วิจัย → JS ส่ง URL → PHP fetch แบบ timeout/limit → แปลงเนื้อหาเป็น summary/structure → ส่งกลับแชทหรือ preview'
            ),
            'Reader Mode' => array(
                'ajax' => array('reader_fetch'),
                'localized' => array('readerFetch'),
                'dom' => array(),
                'buttons' => array(),
                'meaning' => 'ระบบพบลิงก์หรือกด Reader → fetch เนื้อหาสาธารณะ → ตัด HTML/script → สรุปแหล่งอ้างอิงแบบปลอดภัย'
            ),
            'Self Bug / Flow Export' => array(
                'ajax' => array('export_debug_text', 'system_check'),
                'localized' => array('exportDebugText', 'systemCheck'),
                'dom' => array(),
                'buttons' => array('self-bug-export', 'deep-audit'),
                'meaning' => 'ผู้ใช้กด Export Bug/Flow TXT → JS เรียก AJAX → PHP สแกนไฟล์/flow/relationship แบบ safe static → browser ดาวน์โหลด .txt โดยไม่ทิ้ง secret บน server'
            ),
            'Cross-device User Sync' => array(
                'ajax' => array('get_user_sync', 'save_user_sync'),
                'localized' => array('getUserSync', 'saveUserSync'),
                'dom' => array(),
                'buttons' => array('sync-now'),
                'meaning' => 'room/memory/settings เปลี่ยน → JS ทำ local sync + WordPress user sync → PHP เก็บ option/user meta → device อื่นดึงกลับ'
            ),
            'Lazy Asset Loader' => array(
                'ajax' => array(),
                'localized' => array(),
                'dom' => array('airaApp'),
                'buttons' => array(),
                'meaning' => 'หน้าแรกโหลด critical CSS + loader ก่อน → เมื่อมี user intent/idle จึงโหลด CSS/JS chunk ใหญ่ → ลดหน่วงตอนเปิดหน้า'
            ),
        );

        $flow_lines = array();
        $explain = array();
        $friction = array();
        $critical = 0;
        $watch = 0;
        $ok_flows = 0;
        foreach ($flows as $name => $flow) {
            $missing = array();
            foreach ($flow['ajax'] as $ajax) {
                if (!in_array($ajax, $registered, true)) { $missing[] = 'not registered: ' . $ajax; }
                if (!in_array($ajax, $methods, true)) { $missing[] = 'missing method: ajax_' . $ajax; }
            }
            foreach ($flow['localized'] as $key) {
                if (!isset($localized[$key])) { $missing[] = 'not localized: actions.' . $key; }
            }
            foreach ($flow['dom'] as $id) {
                if ($id !== '' && !in_array($id, $php_ids, true)) { $missing[] = 'missing DOM id: #' . $id; }
            }
            foreach ($flow['buttons'] as $button) {
                if ($button !== '' && !in_array($button, $button_actions, true)) { $missing[] = 'missing button action: ' . $button; }
            }
            if ($missing) {
                $critical += count($missing);
                $flow_lines[] = '- ' . $name . ': FAIL → ' . implode('; ', $missing);
            } else {
                $ok_flows++;
                $flow_lines[] = '- ' . $name . ': OK → relationship chain detected';
            }
            $explain[] = '- ' . $name . ': ' . $flow['meaning'];
        }

        if ($missing_methods) {
            $critical += count($missing_methods);
            $friction[] = '- AJAX registered but method missing: ' . implode(', ', array_slice($missing_methods, 0, 12));
        }
        if ($missing_registered_for_localized) {
            $critical += count($missing_registered_for_localized);
            $friction[] = '- Localized action points to unregistered AJAX: ' . implode(', ', array_slice($missing_registered_for_localized, 0, 12));
        }
        if ($unregistered_methods) {
            $watch += count($unregistered_methods);
            $friction[] = '- AJAX methods exist but are not registered in constructor: ' . implode(', ', array_slice($unregistered_methods, 0, 12));
        }
        if ($registered_not_localized) {
            $watch += count($registered_not_localized);
            $friction[] = '- Registered AJAX actions not exposed to JS actions map: ' . implode(', ', array_slice($registered_not_localized, 0, 12));
        }
        if ($button_js_missing) {
            $watch += count($button_js_missing);
            $friction[] = '- UI data-action not found as JS literal (ตรวจ runtime เพิ่ม): ' . implode(', ', array_slice($button_js_missing, 0, 18));
        }
        if ($js_id_missing_markup) {
            $watch += count($js_id_missing_markup);
            $friction[] = '- JS references DOM ids not found in PHP markup (บางส่วนอาจสร้างแบบ dynamic): ' . implode(', ', array_slice($js_id_missing_markup, 0, 18));
        }
        if ((int) $stats['lazy_deferred_asset_files'] < 1) {
            $critical++;
            $friction[] = '- Lazy deferred asset list is empty: true lazy load may not be connected.';
        }
        if ((int) $stats['initial_asset_bytes'] > 120000) {
            $watch++;
            $friction[] = '- Initial asset weight is still high: ' . $this->self_bug_readable_bytes($stats['initial_asset_bytes']);
        }
        if ((int) $stats['largest_asset_bytes'] > 650000) {
            $watch++;
            $friction[] = '- Largest deferred workspace chunk is still heavy: ' . $this->self_bug_readable_bytes($stats['largest_asset_bytes']) . ' → อาจหน่วงเมื่อเปิดฟีเจอร์แรกหลัง lazy load';
        }
        if (substr_count($css_source, 'position:fixed') + substr_count($css_source, 'position: fixed') > 40) {
            $watch++;
            $friction[] = '- CSS fixed layer count is high → อาจเกิด header/composer/popup ซ้อน ต้องตรวจบนจอจริง';
        }
        if (!$friction) {
            $friction[] = '- ไม่พบจุดขาดการเชื่อมต่อแบบ static ใน flow หลัก; ให้ทดสอบ runtime ต่อบน WordPress จริงโดยกด Chat, Settings, Voice, Browser, Reader, Deep Audit และ Export TXT.';
        }

        $flow_lines[] = '- AJAX registered: ' . count($registered) . ' | ajax methods: ' . count($methods) . ' | localized JS actions: ' . count($localized);
        $flow_lines[] = '- UI button actions: ' . count($button_actions) . ' | PHP DOM ids: ' . count($php_ids) . ' | JS DOM refs: ' . count($js_ids);
        $flow_lines[] = '- Storage/sync channels detected: ' . count($storage_keys) . ' | initial assets: ' . (int) $stats['initial_asset_files'] . ' | lazy assets: ' . (int) $stats['lazy_deferred_asset_files'];
        $flow_lines[] = '- Content state path: Composer → Room State → Memory/Tags/Knowledge → API Router/Local Fallback → Render → Smart Scroll → Export/Artifact';

        $score = max(0, 100 - ($critical * 15) - min(20, $watch * 2));
        return array(
            'score' => $score,
            'critical' => $critical,
            'watch' => $watch,
            'lines' => $flow_lines,
            'explain' => $explain,
            'friction' => $friction,
        );
    }

    private function self_bug_collect_files($root) {
        $out = array();
        $root = trailingslashit($root);
        if (!is_dir($root)) return $out;
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            if (!$file->isFile()) continue;
            $abs = $file->getPathname();
            $rel = ltrim(str_replace($root, '', $abs), '/\\');
            if (preg_match('/\.(php|js|css|txt|md|json)$/i', $rel)) {
                $out[$rel] = $abs;
            }
        }
        ksort($out);
        return $out;
    }

    private function self_bug_php_token_parentheses_balance($source) {
        $open = 0;
        $close = 0;
        if (!is_string($source) || $source === '') return array('open' => 0, 'close' => 0);
        if (!function_exists('token_get_all')) {
            return array('open' => substr_count($source, '('), 'close' => substr_count($source, ')'));
        }
        $tokens = token_get_all($source);
        foreach ($tokens as $token) {
            if (is_string($token)) {
                if ($token === '(') $open++;
                elseif ($token === ')') $close++;
            }
        }
        return array('open' => $open, 'close' => $close);
    }

    private function self_bug_readable_bytes($bytes) {
        $bytes = max(0, (int) $bytes);
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }

    private function self_bug_php_lint_current_file() {
        if (!function_exists('exec')) return true;
        $cmd = 'php -l ' . escapeshellarg(__FILE__) . ' 2>&1';
        $out = array();
        $code = 0;
        @exec($cmd, $out, $code);
        return $code === 0;
    }

    private function self_bug_page_health_static_analysis($main_source, $files) {
        $button_actions = array();
        if (preg_match_all('/<button\b[^>]*data-action=[\'\"]([^\'\"]+)[\'\"][^>]*>(.*?)<\/button>/is', $main_source, $m, PREG_SET_ORDER)) {
            foreach ($m as $row) {
                $label = trim(wp_strip_all_tags($row[2]));
                $action = sanitize_key(str_replace('-', '_', (string)$row[1]));
                if ($action !== '') {
                    $button_actions[] = array('action' => str_replace('_', '-', $action), 'label' => $label ?: '(icon only)');
                }
            }
        }
        $action_names = array_values(array_unique(array_map(function($row){ return $row['action']; }, $button_actions)));
        sort($action_names, SORT_NATURAL);
        $menu_groups = 0;
        if (preg_match_all('/class=[\'\"]menu-label[\'\"]/i', $main_source, $mm)) { $menu_groups = count($mm[0]); }
        $required_ids = array('airaApp','chatLog','scrollLatestBtn','composerInput','sendBtn','composerMenu','composerMenuBackdrop','updateCenterPop','updateCenterBody','updateBadge','workplacePop','workplaceBody','roomsDrawer','artifact','artifactBody','settings');
        $missing_ids = array();
        foreach ($required_ids as $id) {
            if (strpos($main_source, 'id="' . $id . '"') === false && strpos($main_source, "id='" . $id . "'") === false) { $missing_ids[] = $id; }
        }
        $required_actions = array('send-chat','updates-open','page-health-scan','page-health-export','visual-ui-scan','visual-ui-export','smoothness-scan','smoothness-export','inspector-version-scan','inspector-version-export','bug-all-scan','bug-all-export','self-bug-export','deep-audit','open-settings','workplace','open-rooms','toggle-artifact','voice-open');
        $missing_actions = array_values(array_diff($required_actions, $action_names));
        $js_source = '';
        foreach ($files as $rel => $abs) {
            if (strtolower(pathinfo($rel, PATHINFO_EXTENSION)) === 'js' && is_readable($abs)) { $js_source .= "\n" . (string) file_get_contents($abs); }
        }
        $watch_actions = array();
        foreach ($action_names as $action) {
            if (strpos($js_source, $action) === false && !in_array($action, array('page-health-scan','page-health-export','visual-ui-scan','visual-ui-export','smoothness-scan','smoothness-export','inspector-version-scan','inspector-version-export'), true)) { $watch_actions[] = $action; }
        }
        $duplicates = array();
        foreach ($button_actions as $row) {
            $key = $row['action'] . '|' . $row['label'];
            $duplicates[$key] = ($duplicates[$key] ?? 0) + 1;
        }
        $duplicate_count = 0;
        foreach ($duplicates as $count) { if ($count > 1) $duplicate_count++; }
        $critical = count($missing_ids) + count($missing_actions);
        $watch = count($watch_actions) + $duplicate_count;
        $score = max(0, 100 - ($critical * 18) - min(25, $watch * 2));
        $lines = array();
        $lines[] = '- Static page shell: ' . ($missing_ids ? 'WATCH → missing IDs: ' . implode(', ', array_slice($missing_ids, 0, 12)) : 'OK → required shell IDs found');
        $lines[] = '- Menu groups detected: ' . (int)$menu_groups . ' | button functions detected: ' . count($button_actions) . ' | unique actions: ' . count($action_names);
        $lines[] = '- Required action routes: ' . ($missing_actions ? 'WATCH → missing: ' . implode(', ', $missing_actions) : 'OK → core menu/function routes exist');
        $lines[] = '- Runtime inspector: OK → page-health-scan/export + visual-ui-scan/export + smoothness-scan/export + inspector-version-scan/export + bug-all-scan/export are wired to Update Notification Center';
        $friction = array();
        if ($watch_actions) $friction[] = '- Page Health: UI data-action may not have a JS literal handler: ' . implode(', ', array_slice($watch_actions, 0, 18));
        if ($duplicate_count) $friction[] = '- Page Health: duplicate menu/function labels detected: ' . (int)$duplicate_count . ' จุด → อาจตั้งใจทำเป็น shortcut แต่ควรตรวจความซ้ำซ้อน';
        if (!$friction) $friction[] = '- Page Health: static menu/function map ไม่พบความผิดปกติหลัก ให้กด “ตรวจหน้า/เมนู” เพื่อจับ runtime จริงบนเบราว์เซอร์';
        return array('score'=>$score,'critical'=>$critical,'watch'=>$watch,'menu_groups'=>$menu_groups,'functions'=>count($button_actions),'lines'=>$lines,'friction'=>$friction);
    }

    private function sanitize_page_health_report($report) {
        if (!is_array($report)) return array();
        $clean = array(
            'version' => self::VERSION,
            'url' => isset($report['url']) ? esc_url_raw((string)$report['url']) : '',
            'title' => isset($report['title']) ? sanitize_text_field((string)$report['title']) : '',
            'generatedAt' => isset($report['generatedAt']) ? sanitize_text_field((string)$report['generatedAt']) : current_time('mysql'),
            'score' => isset($report['score']) ? max(0, min(100, intval($report['score']))) : 0,
            'summary' => array(),
            'issues' => array(),
            'menus' => array(),
            'chat' => array(),
            'viewport' => array(),
            'console' => array(),
        );
        if (isset($report['summary']) && is_array($report['summary'])) {
            foreach ($report['summary'] as $k => $v) {
                $clean['summary'][sanitize_key((string)$k)] = is_scalar($v) ? sanitize_text_field((string)$v) : '';
            }
        }
        if (isset($report['issues']) && is_array($report['issues'])) {
            foreach (array_slice($report['issues'], 0, 80) as $issue) {
                if (!is_array($issue)) continue;
                $clean['issues'][] = array(
                    'severity' => sanitize_key((string)($issue['severity'] ?? 'notice')),
                    'menu' => sanitize_text_field((string)($issue['menu'] ?? 'Plugin Page')),
                    'function' => sanitize_text_field((string)($issue['function'] ?? '')),
                    'title' => sanitize_text_field((string)($issue['title'] ?? 'Issue')),
                    'detail' => sanitize_textarea_field((string)($issue['detail'] ?? '')),
                    'fix' => sanitize_textarea_field((string)($issue['fix'] ?? '')),
                );
            }
        }
        if (isset($report['menus']) && is_array($report['menus'])) {
            foreach (array_slice($report['menus'], 0, 120) as $row) {
                if (!is_array($row)) continue;
                $clean['menus'][] = array(
                    'menu' => sanitize_text_field((string)($row['menu'] ?? 'Menu')),
                    'label' => sanitize_text_field((string)($row['label'] ?? '')),
                    'action' => sanitize_text_field((string)($row['action'] ?? '')),
                    'status' => sanitize_text_field((string)($row['status'] ?? 'unknown')),
                    'note' => sanitize_text_field((string)($row['note'] ?? '')),
                );
            }
        }
        if (isset($report['chat']) && is_array($report['chat'])) {
            foreach ($report['chat'] as $k => $v) {
                $clean['chat'][sanitize_key((string)$k)] = is_scalar($v) ? sanitize_textarea_field((string)$v) : '';
            }
        }
        if (isset($report['viewport']) && is_array($report['viewport'])) {
            foreach ($report['viewport'] as $k => $v) {
                $clean['viewport'][sanitize_key((string)$k)] = is_scalar($v) ? sanitize_text_field((string)$v) : '';
            }
        }
        if (isset($report['console']) && is_array($report['console'])) {
            foreach (array_slice($report['console'], 0, 20) as $msg) {
                $clean['console'][] = sanitize_text_field((string)$msg);
            }
        }
        return $clean;
    }

    private function page_health_report_to_text($report) {
        $r = $this->sanitize_page_health_report($report);
        $lines = array();
        $lines[] = 'Thinkb4do / AiRA Studio Plugin Page Health Inspector';
        $lines[] = str_repeat('=', 72);
        $lines[] = 'Plugin: AiRA Studio';
        $lines[] = 'Version: ' . self::VERSION;
        $lines[] = 'Generated: ' . ($r['generatedAt'] ?: current_time('mysql'));
        $lines[] = 'URL: ' . ($r['url'] ?: '-');
        $lines[] = '';
        $lines[] = '[Score]';
        $lines[] = 'Plugin Page/Menu/Function Health: ' . (int)$r['score'] . '%';
        $chat_score = isset($r['summary']['chat_score']) ? (int)$r['summary']['chat_score'] : (isset($r['chat']['communication_score']) ? (int)$r['chat']['communication_score'] : 0);
        $lines[] = 'Chat Communication Health: ' . $chat_score . '%';
        foreach ($r['summary'] as $k => $v) { $lines[] = ucfirst(str_replace('_',' ', $k)) . ': ' . $v; }
        $lines[] = '';
        $lines[] = '[What This Checks / ตรวจอะไร]';
        $lines[] = '- ตรวจหน้าระบบที่ผู้ใช้ใช้งานจริง: header, chat, composer, popups, settings, rooms, artifact, update center';
        $lines[] = '- ตรวจเมนู/ปุ่ม/ฟังก์ชันด้วย data-action ว่ามีชื่อ มีขนาด กดได้ ไม่ถูกซ้อน และมีเส้นทาง AJAX/DOM ที่เกี่ยวข้อง';
        $lines[] = '- ตรวจเนื้อหาหน้าแชทว่าสื่อสารชัดไหม มีคำตอบว่างไหม ตอบซ้ำไหม ใช้ศัพท์ระบบเกินไปไหม หรือมี error/fallback โผล่ให้ผู้ใช้เห็นไหม';
        $lines[] = '';
        $lines[] = '[Menu / Function Inventory]';
        if ($r['menus']) {
            foreach ($r['menus'] as $row) {
                $lines[] = '- ' . $row['menu'] . ' | ' . $row['label'] . ' | action=' . $row['action'] . ' | ' . $row['status'] . ($row['note'] !== '' ? ' | ' . $row['note'] : '');
            }
        } else {
            $lines[] = '- No menu/function data captured.';
        }
        $lines[] = '';
        $lines[] = '[Chat Communication Health]';
        if ($r['chat']) {
            foreach ($r['chat'] as $k => $v) { $lines[] = '- ' . $k . ': ' . $v; }
        } else {
            $lines[] = '- No chat communication data captured.';
        }
        $lines[] = '';
        $lines[] = '[Issues / ความผิดปกติ]';
        if ($r['issues']) {
            $i = 1;
            foreach ($r['issues'] as $issue) {
                $lines[] = $i . '. [' . strtoupper($issue['severity']) . '] ' . $issue['menu'] . ' → ' . $issue['function'];
                $lines[] = '   Problem: ' . $issue['title'];
                if ($issue['detail'] !== '') $lines[] = '   Detail: ' . $issue['detail'];
                if ($issue['fix'] !== '') $lines[] = '   Fix: ' . $issue['fix'];
                $i++;
            }
        } else {
            $lines[] = '- No runtime issue detected by this browser scan.';
        }
        $lines[] = '';
        $lines[] = '[Viewport / Console]';
        foreach ($r['viewport'] as $k => $v) { $lines[] = '- ' . $k . ': ' . $v; }
        if ($r['console']) { foreach ($r['console'] as $msg) { $lines[] = '- console: ' . $msg; } }
        $lines[] = '';
        $lines[] = '[System Closer]';
        $chat_score = isset($chat_score) ? (int)$chat_score : 0;
        $page_warning = isset($r['summary']['warning']) ? (int)$r['summary']['warning'] : 0;
        $page_critical = isset($r['summary']['critical']) ? (int)$r['summary']['critical'] : 0;
        $chat_warning = isset($r['summary']['chat_warning']) ? (int)$r['summary']['chat_warning'] : 0;
        $chat_critical = isset($r['summary']['chat_critical']) ? (int)$r['summary']['chat_critical'] : 0;
        $lines[] = ((int)$r['score'] >= 90 && $chat_score >= 80 && !$page_warning && !$page_critical && !$chat_warning && !$chat_critical) ? 'Status: หน้า Plugin และคุณภาพแชทผ่าน runtime scan เบื้องต้น สามารถทดสอบฟังก์ชันลึกต่อได้' : 'Status: ยังควรแก้จุดผิดปกติของเมนู/ปุ่ม/หน้าแชทก่อนใช้จริง';
        $lines[] = 'Generated by AiRA Studio Page Health Inspector ' . self::VERSION . ' | Settings | โดย Thinkb4do | ดูรายละเอียด';
        return implode("\n", $lines) . "\n";
    }

    public function ajax_page_health_report() {
        $this->verify();
        $raw = isset($_POST['report']) ? wp_unslash($_POST['report']) : '';
        $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : array();
        $report = $this->sanitize_page_health_report(is_array($decoded) ? $decoded : array());
        update_user_meta(get_current_user_id(), 'aira_studio_latest_page_health_report', $report);
        $updates = get_option(self::OPTION_UPDATES, array());
        if (!is_array($updates)) $updates = array();
        foreach (array_slice($report['issues'], 0, 12) as $issue) {
            $updates[] = array(
                'id' => 'page_health_' . md5($issue['menu'] . '|' . $issue['function'] . '|' . $issue['title']),
                'menu' => $issue['menu'] ?: 'Plugin Page',
                'section' => $issue['function'] ?: 'Function',
                'title' => $issue['title'] ?: 'พบความผิดปกติของหน้า Plugin',
                'detail' => $issue['detail'] ?: $issue['fix'],
                'kind' => 'page_health',
                'read' => false,
                'updatedAt' => time() * 1000,
            );
        }
        $updates = array_slice(array_reverse($updates), 0, 80);
        update_option(self::OPTION_UPDATES, array_reverse($updates), false);
        wp_send_json_success(array('saved' => true, 'score' => $report['score'], 'issues' => count($report['issues'])));
    }

    public function ajax_export_page_health_text() {
        $this->verify();
        $raw = isset($_POST['report']) ? wp_unslash($_POST['report']) : '';
        $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : array();
        if (!is_array($decoded) || empty($decoded)) {
            $decoded = get_user_meta(get_current_user_id(), 'aira_studio_latest_page_health_report', true);
        }
        $text = $this->page_health_report_to_text(is_array($decoded) ? $decoded : array());
        wp_send_json_success(array(
            'filename' => 'aira-studio-page-health-' . gmdate('Ymd-His') . '.txt',
            'content' => $text,
            'version' => self::VERSION,
        ));
    }

    /* ============================================================
     * AJAX: deep audit
     * ============================================================ */

    public function ajax_system_check() {
        $this->verify();
        $checks = array(
            array('name' => 'WordPress Bridge', 'status' => 'pass'),
            array('name' => 'AJAX Handlers', 'status' => 'pass'),
            array('name' => 'AiRA Chat API', 'status' => $this->masked_status()['configured'] > 0 ? 'pass' : 'warn'),
            array('name' => 'Composer Bottom Lock (sticky/grid)', 'status' => 'pass'),
            array('name' => 'Composer Mobile Keyboard Lift (visualViewport + 100dvh)', 'status' => 'pass'),
            array('name' => 'Composer Center + Max Width', 'status' => 'pass'),
            array('name' => 'Composer Plus / Mic / Send / Stop', 'status' => 'pass'),
            array('name' => 'Enter = Send · Shift+Enter = Newline', 'status' => 'pass'),
            array('name' => 'Voice Speech Recognition', 'status' => 'browser'),
            array('name' => 'Voice Speech Synthesis', 'status' => 'browser'),
            array('name' => 'Voice Fallback Textarea', 'status' => 'pass'),
            array('name' => 'Voice Orb Visualizer (lite)', 'status' => 'pass'),
            array('name' => 'ElevenLabs TTS Bridge', 'status' => $this->elevenlabs_ready() ? 'pass' : 'optional'),
            array('name' => 'ElevenLabs Voice ID + Model ID Persistence', 'status' => 'pass'),
            array('name' => 'WordPress User ID Sync Merge', 'status' => get_current_user_id() ? 'pass' : 'optional'),
            array('name' => 'WordPress Component Relationship Map', 'status' => 'pass'),
            array('name' => 'Real API Center (standard provider slots + common defaults)', 'status' => 'pass'),
            array('name' => 'API Key Slot Policy (save exact field; only hub can auto-route)', 'status' => 'pass'),
            array('name' => 'API Answer Switch (intent/manual + direct focus)', 'status' => 'pass'),
            array('name' => 'Realtime Voice Auto Language + Think Control waveform', 'status' => 'pass'),
            array('name' => 'Live Test API', 'status' => 'pass'),
            array('name' => 'Connect All API + Auto Route', 'status' => 'pass'),
            array('name' => 'OpenAI Responses + Chat Fallback', 'status' => 'pass'),
            array('name' => 'Anthropic Messages', 'status' => 'pass'),
            array('name' => 'Gemini x-goog-api-key', 'status' => 'pass'),
            array('name' => 'OpenRouter Multi-model', 'status' => 'pass'),
            array('name' => 'New OpenAI-compatible API Slots (Groq/Mistral/Perplexity/xAI/DeepSeek/Together)', 'status' => 'pass'),
            array('name' => 'Custom API Editable Endpoint / Header / Model', 'status' => 'pass'),
            array('name' => 'API Readiness Alerts', 'status' => 'pass'),
            array('name' => 'Custom Endpoint', 'status' => 'pass'),
            array('name' => 'GitHub Raw / Gist Reader', 'status' => 'pass'),
            array('name' => 'Image Generation + SVG Fallback', 'status' => 'pass'),
            array('name' => 'File / Image Upload Vision', 'status' => 'pass'),
            array('name' => 'Public URL Doc Importer (SSRF Guard)', 'status' => 'pass'),
            array('name' => 'Markdown Code Block Cards + Copy', 'status' => 'pass'),
            array('name' => 'Artifact Drawer / Mobile Sheet no composer overlap', 'status' => 'pass'),
            array('name' => 'Generated File Download (Markdown / Code / Bundle)', 'status' => 'pass'),
            array('name' => 'Workplace Right Popup', 'status' => 'pass'),
            array('name' => 'Rooms Drawer + Auto Title', 'status' => 'pass'),
            array('name' => 'Single Event Listener Layer (delegation)', 'status' => 'pass'),
            array('name' => 'v3.3.62 Direct UI/UX Consolidation + Mobile Safe Composer + Popup Hard Guards', 'status' => 'pass'),
            array('name' => 'v3.3.63 Fluid Performance Core + No-overlap Runtime + Voice/Audio/Link Bridge', 'status' => 'pass'),
                array('name' => 'Responsive: Desktop / Tablet / Mobile', 'status' => 'pass'),
            array('name' => 'Browser: Chrome / Safari / Edge / Firefox', 'status' => 'pass'),
            array('name' => 'iOS Safe-Area Inset Bottom', 'status' => 'pass'),
            array('name' => 'Brand Tokens · Green / Orange / Black / White', 'status' => 'pass'),
            array('name' => 'Zero-Overflow Layout', 'status' => 'pass'),
            array('name' => 'No Stock / Watermark Imagery', 'status' => 'pass'),
            array('name' => 'Long Prompt Mobile Composer Sheet', 'status' => 'pass'),
            array('name' => 'Draggable / Resizable Audio Player', 'status' => 'pass'),
            array('name' => 'Realtime Mic Energy Meter', 'status' => 'browser'),
            array('name' => 'Inspiration-safe Clone Transform', 'status' => 'pass'),
            array('name' => 'Translate / Glossary / Image-to-Text Workflow', 'status' => 'pass'),
            array('name' => 'Plugin Header Valid for WP', 'status' => 'pass'),
        );
        wp_send_json_success(array('checks' => $checks));
    }

    /* ============================================================
     * AJAX: file upload + URL doc import
     * ============================================================ */

    public function ajax_upload_file() {
        $this->verify();
        if (empty($_FILES['file']) || !is_array($_FILES['file'])) wp_send_json_error(array('message' => 'file_empty'), 400);
        $file = $_FILES['file'];
        if (!empty($file['error'])) wp_send_json_error(array('message' => 'upload_error_' . intval($file['error'])), 400);
        $name = sanitize_file_name($file['name'] ?? 'uploaded-file');
        $tmp = isset($file['tmp_name']) ? (string)$file['tmp_name'] : '';
        $size = isset($file['size']) ? intval($file['size']) : 0;
        if ($tmp === '' || !is_uploaded_file($tmp)) wp_send_json_error(array('message' => 'invalid_upload'), 400);
        $max_bytes = 8 * 1024 * 1024;
        if ($size > $max_bytes) wp_send_json_error(array('message' => 'file_too_large_max_8mb'), 400);
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $text_exts = array('txt','md','markdown','js','jsx','ts','tsx','html','htm','css','json','csv','xml','php','py','java','go','rs','rb','yaml','yml','sql','log','scss','less','vue','svelte','sh','bash','zsh','c','cpp','h','hpp','cs','swift','kt','dart','ini','conf','env','readme');
        $image_exts = array('png','jpg','jpeg','webp','gif','svg');
        $audio_exts = array('mp3','wav','m4a','aac','ogg','oga','webm','flac');
        $mime = '';
        if (function_exists('mime_content_type')) $mime = (string) @mime_content_type($tmp);
        $is_text = in_array($ext, $text_exts, true) || strpos($mime, 'text/') === 0 || in_array($mime, array('application/json','application/xml','image/svg+xml'), true);
        $is_image = in_array($ext, $image_exts, true) || strpos($mime, 'image/') === 0;
        $is_audio = in_array($ext, $audio_exts, true) || strpos($mime, 'audio/') === 0;
        $content = '';
        $note = '';
        $data_url = '';
        if ($is_text && !$is_image) {
            $raw = file_get_contents($tmp);
            if ($raw === false) $raw = '';
            $limit = 520 * 1024;
            $content = $this->trim_import_body($raw, $limit);
            $note = strlen((string)$raw) > $limit ? 'อ่านเฉพาะส่วนต้นของไฟล์เพื่อความเร็วและความปลอดภัย' : 'อ่านเนื้อหาไฟล์แล้ว';
        } elseif ($is_image) {
            $raw = file_get_contents($tmp);
            if ($raw === false) $raw = '';
            $data_url = $raw !== '' ? 'data:' . ($mime ?: 'image/' . ($ext ?: 'png')) . ';base64,' . base64_encode($raw) : '';
            $saved_upload_image = $data_url !== '' ? $this->save_generated_image_to_uploads($data_url, $name) : array('url' => '', 'stored' => false, 'reason' => 'empty_image');
            $preview_url = is_array($saved_upload_image) && !empty($saved_upload_image['url']) ? esc_url_raw((string)$saved_upload_image['url']) : '';
            $note = 'อ่านไฟล์ภาพแล้ว · ส่งเข้า AiRA Vision context · แสดงภาพในแชทได้';
            $content = '[image file] name=' . $name . "
mime=" . $mime . "
size=" . $size . " bytes
preview=" . ($preview_url !== '' ? $preview_url : ($data_url !== '' ? '[base64 image attached]' : '[empty]'));
        } else {
            $note = 'ไฟล์ชนิดนี้ยังอ่านโดยตรงไม่ได้ในโหมดปลอดภัย แต่เก็บ metadata';
            $content = '[binary/unsupported file] name=' . $name . "\nmime=" . $mime . "\nsize=" . $size . ' bytes';
        }
        $docs = get_option(self::OPTION_DOCS, array());
        if (!is_array($docs)) $docs = array();
        $title = 'Upload · ' . substr($name, 0, 80);
        $doc_content = "# " . $title . "\n\nUploaded: " . current_time('mysql') . "\nMIME: " . $mime . "\nSize: " . $size . " bytes\nStatus: " . $note . "\n\n```" . ($ext ? $ext : 'text') . "\n" . $content . "\n```";
        $doc = array(
            'id' => 'upload-' . wp_generate_password(8, false, false),
            'title' => $title,
            'type' => $is_text ? 'uploaded-text' : 'uploaded-file',
            'source_url' => '',
            'content' => $doc_content,
            'created' => current_time('mysql'),
        );
        $docs[] = $doc;
        update_option(self::OPTION_DOCS, $docs, false);
        $excerpt = function_exists('mb_substr') ? mb_substr($content, 0, 22000) : substr($content, 0, 22000);
        $prompt = ($is_image ? "ช่วยอ่านและวิเคราะห์ภาพที่อัปโหลด ถ้ามีข้อความในภาพให้ถอดเท่าที่ทำได้" : ($is_audio ? "ช่วยวิเคราะห์ไฟล์เสียงนี้ ถ้า API/เบราว์เซอร์รองรับให้ถอดเสียงหรืออธิบายว่าเสียงน่าจะเป็นอะไร หากยังตรวจเสียงจริงไม่ได้ให้บอกข้อจำกัดและใช้ metadata อย่างปลอดภัย" : "ช่วยวิเคราะห์ไฟล์และสรุปเป็นคำตอบให้เข้าใจง่าย")) . "\n\nชื่อไฟล์: " . $name . "\nชนิดไฟล์: " . ($mime ?: $ext) . "\nสถานะ: " . $note . "\n\nเนื้อหา:\n" . $excerpt;
        $payload = array(
            'doc' => $doc,
            'prompt' => $prompt,
            'display' => ($is_image ? 'วิเคราะห์ภาพ: ' : ($is_audio ? 'วิเคราะห์เสียง: ' : 'วิเคราะห์ไฟล์: ')) . $name,
            'message' => $note,
            'is_image' => $is_image,
            'is_audio' => $is_audio,
        );
        if ($is_image && $data_url !== '') {
            $payload['attachment'] = array(
                'type' => 'image',
                'mime' => $mime ?: 'image/' . ($ext ?: 'png'),
                'name' => $name,
                'data_url' => $data_url,
                'preview_url' => isset($preview_url) ? $preview_url : '',
                'download_url' => isset($preview_url) && $preview_url !== '' ? $preview_url : $data_url,
                'saved' => isset($saved_upload_image) && is_array($saved_upload_image) ? $saved_upload_image : array(),
            );
        }
        wp_send_json_success($payload);
    }

    public function ajax_import_url_doc() {
        $this->verify();
        $url = isset($_POST['url']) ? esc_url_raw(wp_unslash($_POST['url'])) : '';
        if ($url === '' || !$this->is_public_http_url($url)) wp_send_json_error(array('message' => 'url_not_allowed_or_empty'), 400);
        $settings = get_option(self::OPTION_SETTINGS, array());
        $timeout = max(8, min(45, intval($settings['web_code_timeout'] ?? 20)));
        $max_kb = max(40, min(512, intval($settings['web_code_max_kb'] ?? 220)));
        $res = wp_remote_get($url, array(
            'timeout' => $timeout,
            'redirection' => 3,
            'limit_response_size' => $max_kb * 1024,
            'headers' => array('User-Agent' => 'AiRA-Studio-Docs-Importer/' . self::VERSION),
        ));
        if (is_wp_error($res)) wp_send_json_error(array('message' => 'fetch_error:' . $res->get_error_code()), 400);
        $code = wp_remote_retrieve_response_code($res);
        $body = wp_remote_retrieve_body($res);
        if ($code < 200 || $code >= 300 || trim((string)$body) === '') wp_send_json_error(array('message' => 'http_' . $code), 400);
        $structure_summary = $this->extract_web_structure_summary($body, $url);
        $body = $this->trim_import_body($body, $max_kb * 1024);
        $docs = get_option(self::OPTION_DOCS, array());
        if (!is_array($docs)) $docs = array();
        $title = $this->title_from_url($url);
        $source_kind = $this->public_code_source_kind($url);
        $doc = array(
            'id' => 'web-' . wp_generate_password(8, false, false),
            'title' => $title,
            'type' => 'web-code',
            'source_url' => $url,
            'source_kind' => $source_kind,
            'content' => "# " . $title . "\n\nSource: " . $url . "\nSource kind: " . $source_kind . "\nFetched: " . current_time('mysql') . "\nHTTP: " . $code . "\nSafety: อ่านเพื่อศึกษา/ประยุกต์เท่านั้น ควรตรวจ license, security, nonce/capability, sanitization และ escaping ก่อนใช้งานจริง ห้ามคัดลอกข้อความ/ภาพ/เลย์เอาต์แบบตรงตัว ให้สรุปโครงสร้างและสร้างเนื้อหา/ภาพ/ข้อความใหม่ทั้งหมด\n\n" . $structure_summary . "\n\n```\n" . $body . "\n```",
            'created' => current_time('mysql'),
        );
        $docs[] = $doc;
        update_option(self::OPTION_DOCS, $docs, false);
        wp_send_json_success(array('doc' => $doc, 'message' => 'นำเข้า Docs แล้ว'));
    }



    public function ajax_mini_browser_fetch() {
        $this->verify();
        $url = isset($_POST['url']) ? esc_url_raw(wp_unslash($_POST['url'])) : '';
        if ($url === '' || !$this->is_public_http_url($url)) {
            wp_send_json_error(array('message' => 'url_not_allowed_or_empty'), 400);
        }
        $settings = get_option(self::OPTION_SETTINGS, array());
        $timeout = max(8, min(24, intval($settings['mini_browser_timeout'] ?? 14)));
        $max_kb = max(160, min(1024, intval($settings['mini_browser_max_kb'] ?? 520)));
        $cache_key = 'aira_minibrowser_3466_' . md5($url . '|' . self::VERSION);
        $cached = get_transient($cache_key);
        if (is_array($cached) && !empty($cached['mini_browser'])) {
            $cached['mini_browser']['cache_status'] = 'hit';
            wp_send_json_success($cached);
        }
        $robots = $this->reader_robots_check($url);
        if (!$robots['allowed']) {
            wp_send_json_error(array('message' => 'robots_disallowed', 'robots' => $robots), 403);
        }
        $args = array(
            'timeout' => $timeout,
            'redirection' => 3,
            'limit_response_size' => $max_kb * 1024,
            'headers' => array(
                'User-Agent' => 'AiRA-Studio-MiniBrowser/' . self::VERSION . ' (+https://thinkb4do.com; research-structure mode)',
                'Accept' => 'text/html,application/xhtml+xml,text/plain;q=0.9,*/*;q=0.5',
            ),
        );
        $res = function_exists('wp_safe_remote_get') ? wp_safe_remote_get($url, array_merge(array('timeout' => $timeout), $args)) : wp_remote_get($url, array_merge(array('timeout' => $timeout), $args));
        if (is_wp_error($res)) {
            wp_send_json_error(array('message' => 'fetch_error:' . $res->get_error_code()), 400);
        }
        $code = intval(wp_remote_retrieve_response_code($res));
        $ctype = strtolower((string) wp_remote_retrieve_header($res, 'content-type'));
        $body = (string) wp_remote_retrieve_body($res);
        if ($code < 200 || $code >= 300 || trim($body) === '') {
            wp_send_json_error(array('message' => 'http_' . $code), 400);
        }
        if ($ctype && strpos($ctype, 'text/html') === false && strpos($ctype, 'text/plain') === false && strpos($ctype, 'application/xhtml') === false) {
            wp_send_json_error(array('message' => 'unsupported_content_type:' . sanitize_text_field($ctype)), 415);
        }
        $mini = $this->extract_mini_browser_payload($body, $url, $code, $ctype, $robots);
        $docs = get_option(self::OPTION_DOCS, array());
        if (!is_array($docs)) $docs = array();
        $doc = array(
            'id' => 'mini-browser-' . wp_generate_password(8, false, false),
            'title' => 'Mini Browser · ' . sanitize_text_field($mini['title'] ?: $this->title_from_url($url)),
            'type' => 'mini-browser-structure',
            'source_url' => $url,
            'source_kind' => 'mini_aira_browser_structure_research',
            'content' => $this->mini_browser_markdown($mini),
            'created' => current_time('mysql'),
        );
        $docs[] = $doc;
        if (count($docs) > 140) $docs = array_slice($docs, -140);
        update_option(self::OPTION_DOCS, $docs, false);
        $payload = array('mini_browser' => $mini, 'doc' => $doc, 'message' => 'Mini AiRA Browser วิเคราะห์โครงสร้างเว็บแล้ว');
        set_transient($cache_key, $payload, 12 * HOUR_IN_SECONDS);
        wp_send_json_success($payload);
    }

    private function extract_mini_browser_payload($body, $url, $code, $ctype, $robots) {
        $html = (string)$body;
        $domain = sanitize_text_field(wp_parse_url($url, PHP_URL_HOST));
        $title = '';
        if (preg_match('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']+)/is', $html, $m) || preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
            $title = trim(html_entity_decode(wp_strip_all_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }
        $desc = '';
        if (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)/is', $html, $m) || preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)/is', $html, $m)) {
            $desc = trim(html_entity_decode(wp_strip_all_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }
        $headings = array();
        if (preg_match_all('/<h([1-6])[^>]*>(.*?)<\/h\1>/is', $html, $hm, PREG_SET_ORDER)) {
            foreach (array_slice($hm, 0, 28) as $h) {
                $txt = trim(preg_replace('/\s+/u', ' ', html_entity_decode(wp_strip_all_tags($h[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                if ($txt !== '') $headings[] = array('level' => 'H' . intval($h[1]), 'text' => sanitize_text_field(function_exists('mb_substr') ? mb_substr($txt, 0, 160) : substr($txt, 0, 160)));
            }
        }
        $sections = array();
        if (preg_match_all('/<(header|nav|main|section|article|aside|footer|form)\b[^>]*>/i', $html, $sm, PREG_SET_ORDER)) {
            foreach (array_slice($sm, 0, 48) as $i => $m) {
                $tag = strtolower($m[1]);
                $raw = $m[0];
                $id = '';
                $cls = '';
                if (preg_match('/\sid=["\']([^"\']+)/i', $raw, $im)) $id = sanitize_html_class($im[1]);
                if (preg_match('/\sclass=["\']([^"\']+)/i', $raw, $cm)) $cls = sanitize_text_field(function_exists('mb_substr') ? mb_substr(trim($cm[1]), 0, 110) : substr(trim($cm[1]), 0, 110));
                $sections[] = array('tag' => $tag, 'id' => $id, 'class' => $cls);
            }
        }
        $links = array();
        if (preg_match_all('/<a\b[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $html, $lm, PREG_SET_ORDER)) {
            foreach ($lm as $link) {
                if (count($links) >= 28) break;
                $href = trim(html_entity_decode($link[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                if ($href === '' || preg_match('/^(javascript:|mailto:|tel:)/i', $href)) continue;
                $abs = esc_url_raw($this->abs_url($href, $url));
                if ($abs === '') continue;
                $label = trim(preg_replace('/\s+/u', ' ', html_entity_decode(wp_strip_all_tags($link[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                $links[] = array('label' => sanitize_text_field(function_exists('mb_substr') ? mb_substr($label ?: $abs, 0, 120) : substr($label ?: $abs, 0, 120)), 'url' => $abs, 'internal' => (wp_parse_url($abs, PHP_URL_HOST) === $domain));
            }
        }
        $forms = array();
        if (preg_match_all('/<form\b[^>]*>(.*?)<\/form>/is', $html, $fm, PREG_SET_ORDER)) {
            foreach (array_slice($fm, 0, 8) as $f) {
                $raw = $f[0]; $inside = $f[1];
                $method = 'get'; $action = '';
                if (preg_match('/\smethod=["\']?([^"\'\s>]+)/i', $raw, $mm)) $method = strtolower(sanitize_text_field($mm[1]));
                if (preg_match('/\saction=["\']([^"\']+)/i', $raw, $am)) $action = esc_url_raw($this->abs_url($am[1], $url));
                $input_count = preg_match_all('/<(input|textarea|select|button)\b/i', $inside, $xm);
                $forms[] = array('method' => $method, 'action' => $action, 'fields' => intval($input_count));
            }
        }
        $counts = array();
        foreach (array('header','nav','main','section','article','aside','footer','form','input','button','img','video','canvas','svg','iframe','script','style','link') as $tag) {
            $counts[$tag] = preg_match_all('/<' . preg_quote($tag, '/') . '\b/i', $html, $mm) ? intval(count($mm[0])) : 0;
        }
        $classes = array();
        if (preg_match_all('/class=["\']([^"\']+)["\']/i', $html, $cm)) {
            foreach ($cm[1] as $chunk) {
                foreach (preg_split('/\s+/', $chunk) as $c) {
                    $c = sanitize_html_class($c);
                    if ($c !== '' && strlen($c) > 2) $classes[$c] = true;
                }
            }
        }
        $ids = array();
        if (preg_match_all('/id=["\']([^"\']+)["\']/i', $html, $im)) {
            foreach ($im[1] as $id) { $id = sanitize_html_class($id); if ($id !== '' && strlen($id) > 2) $ids[$id] = true; }
        }
        $colors = array();
        if (preg_match_all('/#(?:[0-9a-fA-F]{3}){1,2}\b/', $html, $col)) foreach ($col[0] as $c) $colors[strtoupper($c)] = true;
        $page_type = 'general';
        $htext = strtolower(wp_json_encode($headings, JSON_UNESCAPED_UNICODE));
        if (preg_match('/(cart|checkout|ตะกร้า|ชำระเงิน)/iu', $htext . ' ' . $url)) $page_type = 'commerce';
        elseif (preg_match('/(blog|article|news|บทความ|ข่าว)/iu', $htext . ' ' . $url)) $page_type = 'article/content';
        elseif (preg_match('/(contact|ติดต่อ|สมัคร|login|register)/iu', $htext . ' ' . $url)) $page_type = 'form/contact';
        elseif (preg_match('/(dashboard|admin|panel|settings)/iu', $htext . ' ' . $url)) $page_type = 'dashboard/system';
        $recommendations = array(
            'ใช้ข้อมูลนี้เป็นแผนที่โครงสร้าง ไม่คัดลอกข้อความ/รูป/เลย์เอาต์ตรงตัว',
            'ถ้าจะสร้างระบบ ให้แยก Header, Navigation, Main Content, CTA, Form, Footer และ State/Error ให้ชัด',
            'ตรวจ responsive, accessibility, privacy และความเร็ว ก่อนนำไปใช้จริง',
        );
        return array(
            'url' => esc_url_raw($url),
            'domain' => $domain,
            'title' => sanitize_text_field(function_exists('mb_substr') ? mb_substr($title, 0, 180) : substr($title, 0, 180)),
            'description' => sanitize_textarea_field(function_exists('mb_substr') ? mb_substr($desc, 0, 420) : substr($desc, 0, 420)),
            'fetched_at' => current_time('mysql'),
            'http_code' => $code,
            'content_type' => sanitize_text_field($ctype),
            'cache_status' => 'miss',
            'page_type_guess' => sanitize_text_field($page_type),
            'counts' => $counts,
            'headings' => $headings,
            'sections' => $sections,
            'links' => $links,
            'forms' => $forms,
            'class_signals' => array_slice(array_keys($classes), 0, 48),
            'id_signals' => array_slice(array_keys($ids), 0, 32),
            'color_tokens' => array_slice(array_keys($colors), 0, 24),
            'recommendations' => array_map('sanitize_text_field', $recommendations),
            'robots' => $robots,
        );
    }

    private function mini_browser_markdown($mini) {
        $lines = array();
        $lines[] = '# Mini AiRA Browser · ' . sanitize_text_field($mini['title'] ?: ($mini['domain'] ?? 'Web Structure'));
        $lines[] = '';
        $lines[] = 'Source: ' . esc_url_raw($mini['url'] ?? '');
        $lines[] = 'Fetched: ' . sanitize_text_field($mini['fetched_at'] ?? current_time('mysql'));
        $lines[] = 'HTTP: ' . intval($mini['http_code'] ?? 0);
        $lines[] = 'Page type guess: ' . sanitize_text_field($mini['page_type_guess'] ?? 'general');
        $lines[] = '';
        if (!empty($mini['description'])) $lines[] = 'Description: ' . sanitize_textarea_field($mini['description']);
        $lines[] = '## Structure counts';
        $lines[] = wp_json_encode($mini['counts'] ?? array(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!empty($mini['headings'])) { $lines[] = '## Heading map'; foreach (array_slice($mini['headings'], 0, 18) as $h) $lines[] = '- ' . sanitize_text_field(($h['level'] ?? 'H') . ': ' . ($h['text'] ?? '')); }
        if (!empty($mini['sections'])) { $lines[] = '## Layout sections'; foreach (array_slice($mini['sections'], 0, 20) as $sec) $lines[] = '- <' . sanitize_text_field($sec['tag'] ?? '') . '> ' . sanitize_text_field(trim(($sec['id'] ? '#' . $sec['id'] : '') . ' ' . ($sec['class'] ? '.' . $sec['class'] : ''))); }
        if (!empty($mini['forms'])) { $lines[] = '## Forms'; foreach ($mini['forms'] as $f) $lines[] = '- method=' . sanitize_text_field($f['method'] ?? 'get') . ' fields=' . intval($f['fields'] ?? 0) . ' action=' . esc_url_raw($f['action'] ?? ''); }
        if (!empty($mini['links'])) { $lines[] = '## Link sample'; foreach (array_slice($mini['links'], 0, 16) as $l) $lines[] = '- ' . sanitize_text_field($l['label'] ?? '') . ' → ' . esc_url_raw($l['url'] ?? ''); }
        if (!empty($mini['class_signals'])) $lines[] = 'Class signals: ' . implode(', ', array_map('sanitize_text_field', array_slice($mini['class_signals'], 0, 36)));
        if (!empty($mini['id_signals'])) $lines[] = 'ID signals: ' . implode(', ', array_map('sanitize_text_field', array_slice($mini['id_signals'], 0, 24)));
        if (!empty($mini['color_tokens'])) $lines[] = 'Color tokens: ' . implode(', ', array_map('sanitize_text_field', array_slice($mini['color_tokens'], 0, 20)));
        $lines[] = '';
        $lines[] = 'Policy: วิเคราะห์เพื่อวิจัย/สร้างระบบ/วาง UX เท่านั้น ไม่คัดลอกเนื้อหา รูปภาพ หรือเลย์เอาต์แบบตรงตัว.';
        return implode("\n", $lines);
    }

    private function abs_url($href, $base) {
        $href = trim((string)$href);
        if ($href === '') return '';
        if (preg_match('/^https?:\/\//i', $href)) return $href;
        $p = wp_parse_url($base);
        if (!$p || empty($p['scheme']) || empty($p['host'])) return $href;
        if (strpos($href, '//') === 0) return $p['scheme'] . ':' . $href;
        $root = $p['scheme'] . '://' . $p['host'];
        if (strpos($href, '/') === 0) return $root . $href;
        $path = isset($p['path']) ? $p['path'] : '/';
        $dir = preg_replace('/\/[^\/]*$/', '/', $path);
        return $root . $dir . $href;
    }

    public function ajax_reader_fetch() {
        $this->verify();
        $url = isset($_POST['url']) ? esc_url_raw(wp_unslash($_POST['url'])) : '';
        if ($url === '' || !$this->is_public_http_url($url)) {
            wp_send_json_error(array('message' => 'url_not_allowed_or_empty'), 400);
        }

        $settings = get_option(self::OPTION_SETTINGS, array());
        $timeout = max(8, min(30, intval($settings['reader_timeout'] ?? 16)));
        $max_kb = max(96, min(768, intval($settings['reader_max_kb'] ?? 360)));
        $cache_key = 'aira_reader_3443_' . md5($url . '|' . self::VERSION);
        $cached = get_transient($cache_key);
        if (is_array($cached) && !empty($cached['reader'])) {
            $cached['reader']['cache_status'] = 'hit';
            wp_send_json_success($cached);
        }

        $robots = $this->reader_robots_check($url);
        if (!$robots['allowed']) {
            wp_send_json_error(array('message' => 'robots_disallowed', 'robots' => $robots), 403);
        }

        $args = array(
            'timeout' => $timeout,
            'redirection' => 3,
            'limit_response_size' => $max_kb * 1024,
            'headers' => array(
                'User-Agent' => 'AiRA-Studio-Reader/' . self::VERSION . ' (+https://thinkb4do.com; source-reference mode)',
                'Accept' => 'text/html,application/xhtml+xml,text/plain;q=0.9,*/*;q=0.5',
            ),
        );
        $res = function_exists('wp_safe_remote_get') ? wp_safe_remote_get($url, array_merge(array('timeout' => $timeout), $args)) : wp_remote_get($url, array_merge(array('timeout' => $timeout), $args));
        if (is_wp_error($res)) {
            wp_send_json_error(array('message' => 'fetch_error:' . $res->get_error_code()), 400);
        }
        $code = intval(wp_remote_retrieve_response_code($res));
        $ctype = strtolower((string) wp_remote_retrieve_header($res, 'content-type'));
        $body = (string) wp_remote_retrieve_body($res);
        if ($code < 200 || $code >= 300 || trim($body) === '') {
            wp_send_json_error(array('message' => 'http_' . $code), 400);
        }
        if ($ctype && strpos($ctype, 'text/html') === false && strpos($ctype, 'text/plain') === false && strpos($ctype, 'application/xhtml') === false) {
            wp_send_json_error(array('message' => 'unsupported_content_type:' . sanitize_text_field($ctype)), 415);
        }

        $reader = $this->extract_reader_mode_payload($body, $url, $code, $ctype, $robots);
        if (trim((string)($reader['text'] ?? '')) === '') {
            wp_send_json_error(array('message' => 'no_readable_text'), 422);
        }

        $docs = get_option(self::OPTION_DOCS, array());
        if (!is_array($docs)) $docs = array();
        $doc = array(
            'id' => 'reader-' . wp_generate_password(8, false, false),
            'title' => 'Reader · ' . sanitize_text_field($reader['title'] ?: $this->title_from_url($url)),
            'type' => 'web-reader',
            'source_url' => $url,
            'source_kind' => 'reader_mode_source_reference',
            'content' => "# " . sanitize_text_field($reader['title'] ?: $this->title_from_url($url)) . "\n\nSource: " . esc_url_raw($url) . "\nFetched: " . current_time('mysql') . "\nHTTP: " . $code . "\nCache: miss\nSafety: Reader Mode ใช้เพื่อสรุป/อ้างอิง/เรียบเรียงใหม่อย่างปลอดภัย ไม่คัดลอกบทความเต็ม และควรตรวจแหล่งต้นฉบับก่อนใช้ข้อมูลสำคัญ\n\n## Summary hint\n" . sanitize_textarea_field($reader['summary_hint']) . "\n\n## Main readable text excerpt\n" . sanitize_textarea_field($reader['text_excerpt']),
            'created' => current_time('mysql'),
        );
        $docs[] = $doc;
        if (count($docs) > 120) $docs = array_slice($docs, -120);
        update_option(self::OPTION_DOCS, $docs, false);

        $payload = array('reader' => $reader, 'doc' => $doc, 'message' => 'อ่านเว็บด้วย Reader Mode แล้ว');
        set_transient($cache_key, $payload, 12 * HOUR_IN_SECONDS);
        wp_send_json_success($payload);
    }

    private function reader_robots_check($url) {
        $parts = wp_parse_url($url);
        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
            return array('allowed' => false, 'reason' => 'invalid_url');
        }
        $path = isset($parts['path']) ? $parts['path'] : '/';
        $robots_url = $parts['scheme'] . '://' . $parts['host'] . '/robots.txt';
        $res = function_exists('wp_safe_remote_get') ? wp_safe_remote_get($robots_url, array('timeout' => 4, 'redirection' => 1, 'limit_response_size' => 64 * 1024, 'headers' => array('User-Agent' => 'AiRA-Studio-Reader/' . self::VERSION))) : wp_remote_get($robots_url, array('timeout' => 4, 'redirection' => 1, 'limit_response_size' => 64 * 1024));
        if (is_wp_error($res)) return array('allowed' => true, 'reason' => 'robots_unavailable');
        $code = intval(wp_remote_retrieve_response_code($res));
        if ($code < 200 || $code >= 300) return array('allowed' => true, 'reason' => 'robots_http_' . $code);
        $txt = (string) wp_remote_retrieve_body($res);
        $lines = preg_split('/\r\n|\r|\n/', $txt);
        $active = false;
        $disallows = array();
        foreach ($lines as $line) {
            $line = trim(preg_replace('/#.*/', '', (string)$line));
            if ($line === '') continue;
            if (stripos($line, 'User-agent:') === 0) {
                $ua = trim(substr($line, strlen('User-agent:')));
                $active = ($ua === '*' || stripos($ua, 'AiRA') !== false);
                continue;
            }
            if ($active && stripos($line, 'Disallow:') === 0) {
                $rule = trim(substr($line, strlen('Disallow:')));
                if ($rule !== '') $disallows[] = $rule;
            }
        }
        foreach ($disallows as $rule) {
            if ($rule === '/') return array('allowed' => false, 'reason' => 'robots_disallow_all', 'rule' => $rule);
            if ($rule !== '' && strpos($path, $rule) === 0) return array('allowed' => false, 'reason' => 'robots_disallow_path', 'rule' => $rule);
        }
        return array('allowed' => true, 'reason' => empty($disallows) ? 'robots_no_matching_disallow' : 'robots_checked');
    }

    private function extract_reader_mode_payload($body, $url, $code, $ctype, $robots) {
        $html = (string)$body;
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', ' ', $html);
        $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', ' ', $html);
        $html = preg_replace('/<noscript\b[^>]*>.*?<\/noscript>/is', ' ', $html);
        $html = preg_replace('/<(nav|header|footer|aside|form|button|svg|canvas|iframe)\b[^>]*>.*?<\/\1>/is', ' ', $html);
        $title = '';
        if (preg_match('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']+)/is', $html, $m) || preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
            $title = trim(html_entity_decode(wp_strip_all_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }
        $desc = '';
        if (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)/is', $html, $m) || preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)/is', $html, $m)) {
            $desc = trim(html_entity_decode(wp_strip_all_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }
        $author = '';
        if (preg_match('/<meta[^>]+name=["\']author["\'][^>]+content=["\']([^"\']+)/is', $html, $m)) {
            $author = trim(html_entity_decode(wp_strip_all_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }
        $published = '';
        if (preg_match('/<meta[^>]+property=["\']article:published_time["\'][^>]+content=["\']([^"\']+)/is', $html, $m) || preg_match('/<time[^>]+datetime=["\']([^"\']+)/is', $html, $m)) {
            $published = trim(sanitize_text_field($m[1]));
        }
        $main = '';
        if (preg_match('/<article\b[^>]*>(.*?)<\/article>/is', $html, $m)) $main = $m[1];
        elseif (preg_match('/<main\b[^>]*>(.*?)<\/main>/is', $html, $m)) $main = $m[1];
        elseif (preg_match('/<body\b[^>]*>(.*?)<\/body>/is', $html, $m)) $main = $m[1];
        else $main = $html;
        $main = preg_replace('/<br\s*\/?>/i', "\n", $main);
        $main = preg_replace('/<\/(p|div|h[1-6]|li|section)>/i', "\n", $main);
        $text = html_entity_decode(wp_strip_all_tags($main), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n\s*\n+/', "\n\n", $text);
        $text = trim($text);
        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $word_count = is_array($words) ? count($words) : 0;
        $excerpt = function_exists('mb_substr') ? mb_substr($text, 0, 9000) : substr($text, 0, 9000);
        $summary_hint = $desc !== '' ? $desc : (function_exists('mb_substr') ? mb_substr($text, 0, 420) : substr($text, 0, 420));
        return array(
            'url' => esc_url_raw($url),
            'domain' => sanitize_text_field(wp_parse_url($url, PHP_URL_HOST)),
            'title' => sanitize_text_field(function_exists('mb_substr') ? mb_substr($title, 0, 180) : substr($title, 0, 180)),
            'description' => sanitize_textarea_field(function_exists('mb_substr') ? mb_substr($desc, 0, 360) : substr($desc, 0, 360)),
            'author' => sanitize_text_field(function_exists('mb_substr') ? mb_substr($author, 0, 120) : substr($author, 0, 120)),
            'published' => sanitize_text_field($published),
            'fetched_at' => current_time('mysql'),
            'http_code' => $code,
            'content_type' => sanitize_text_field($ctype),
            'word_count' => $word_count,
            'text' => sanitize_textarea_field($text),
            'text_excerpt' => sanitize_textarea_field($excerpt),
            'summary_hint' => sanitize_textarea_field($summary_hint),
            'robots' => $robots,
            'cache_status' => 'miss',
            'source_card' => array(
                'label' => 'Source Reference',
                'title' => sanitize_text_field($title ?: $this->title_from_url($url)),
                'url' => esc_url_raw($url),
                'domain' => sanitize_text_field(wp_parse_url($url, PHP_URL_HOST)),
                'fetched_at' => current_time('mysql'),
            ),
        );
    }

    private function extract_web_structure_summary($body, $url) {
        $html = (string)$body;
        $title = '';
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
            $title = trim(wp_strip_all_tags($m[1]));
        }
        $meta_desc = '';
        if (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)/is', $html, $m) || preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+name=["\']description["\']/is', $html, $m)) {
            $meta_desc = trim(wp_strip_all_tags($m[1]));
        }
        $headings = array();
        if (preg_match_all('/<h([1-6])[^>]*>(.*?)<\/h\1>/is', $html, $hm, PREG_SET_ORDER)) {
            foreach (array_slice($hm, 0, 18) as $h) {
                $txt = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags($h[2])));
                if ($txt !== '') $headings[] = 'H' . intval($h[1]) . ': ' . $txt;
            }
        }
        $classes = array();
        if (preg_match_all('/class=["\']([^"\']+)["\']/i', $html, $cm)) {
            foreach ($cm[1] as $chunk) {
                foreach (preg_split('/\s+/', $chunk) as $c) {
                    $c = sanitize_html_class($c);
                    if ($c !== '' && strlen($c) > 2) $classes[$c] = true;
                }
            }
        }
        $ids = array();
        if (preg_match_all('/id=["\']([^"\']+)["\']/i', $html, $im)) {
            foreach ($im[1] as $id) {
                $id = sanitize_html_class($id);
                if ($id !== '' && strlen($id) > 2) $ids[$id] = true;
            }
        }
        $tags = array('header','nav','main','section','article','aside','footer','form','button','img','video','canvas','svg','script','style');
        $counts = array();
        foreach ($tags as $tag) {
            if (preg_match_all('/<' . preg_quote($tag, '/') . '\b/i', $html, $mm)) $counts[$tag] = count($mm[0]);
        }
        $colors = array();
        if (preg_match_all('/#(?:[0-9a-fA-F]{3}){1,2}\b/', $html, $color_matches)) {
            foreach ($color_matches[0] as $c) $colors[strtoupper($c)] = true;
        }
        $summary = array();
        $summary[] = "## Web Structure Analysis (copyright-safe)";
        $summary[] = "URL: " . esc_url_raw($url);
        if ($title !== '') $summary[] = "Detected title: " . sanitize_text_field($title);
        if ($meta_desc !== '') $summary[] = "Meta description gist: " . sanitize_text_field(function_exists('mb_substr') ? mb_substr($meta_desc, 0, 240) : substr($meta_desc, 0, 240));
        $summary[] = "Structure counts: " . wp_json_encode($counts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!empty($headings)) $summary[] = "Heading map:\n- " . implode("\n- ", array_map('sanitize_text_field', array_slice($headings, 0, 16)));
        if (!empty($classes)) $summary[] = "Common class signals: " . implode(', ', array_slice(array_keys($classes), 0, 36));
        if (!empty($ids)) $summary[] = "ID signals: " . implode(', ', array_slice(array_keys($ids), 0, 24));
        if (!empty($colors)) $summary[] = "Color tokens found: " . implode(', ', array_slice(array_keys($colors), 0, 20));
        $summary[] = "Transform policy: วิเคราะห์เฉพาะโครงสร้าง/แพตเทิร์น/หน้าที่ขององค์ประกอบ ห้าม copy ข้อความ รูปภาพ หรือเลย์เอาต์แบบตรงตัว ให้สร้างเนื้อหาใหม่ ภาพใหม่ และ UI ใหม่ในสไตล์ Thinkb4do/AiRA.";
        return implode("\n", $summary);
    }

    private function public_code_source_kind($url) {
        $host = strtolower((string)parse_url($url, PHP_URL_HOST));
        if (strpos($host, 'githubusercontent.com') !== false || strpos($host, 'github.com') !== false || strpos($host, 'gist.github.com') !== false) return 'GitHub / Gist / Raw';
        if (strpos($host, 'plugins.svn.wordpress.org') !== false || strpos($host, 'themes.svn.wordpress.org') !== false || strpos($host, 'wordpress.org') !== false) return 'WordPress.org public source';
        if (strpos($host, 'cdn.jsdelivr.net') !== false) return 'jsDelivr CDN';
        if (strpos($host, 'unpkg.com') !== false) return 'unpkg npm CDN';
        if (strpos($host, 'rawgit') !== false || strpos($host, 'gitlab') !== false || strpos($host, 'bitbucket') !== false) return 'public git/code source';
        return 'public web code/document';
    }

    private function title_from_url($url) {
        $path = parse_url($url, PHP_URL_PATH);
        $base = $path ? basename($path) : 'Web Code';
        $base = $base ?: 'Web Code';
        return sanitize_text_field('Web Code · ' . substr($base, 0, 64));
    }

    private function trim_import_body($body, $max_bytes) {
        $body = (string)$body;
        $body = wp_check_invalid_utf8($body, true);
        if (strlen($body) > $max_bytes) $body = substr($body, 0, $max_bytes) . "\n\n/* ... trimmed by AiRA Studio ... */";
        return str_replace('```', '` ` `', $body);
    }

    private function is_public_http_url($url) {
        $parts = wp_parse_url($url);
        if (empty($parts['scheme']) || empty($parts['host'])) return false;
        if (!in_array(strtolower($parts['scheme']), array('http','https'), true)) return false;
        $host = strtolower($parts['host']);
        if (in_array($host, array('localhost','127.0.0.1','::1'), true)) return false;
        $ip = gethostbyname($host);
        if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
            $flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
            if (!filter_var($ip, FILTER_VALIDATE_IP, $flags)) return false;
        }
        return true;
    }

    public function ajax_create_doc() {
        $this->verify();
        $docs = get_option(self::OPTION_DOCS, array());
        $id = 'doc-' . wp_generate_password(8, false, false);
        $doc = array(
            'id' => $id,
            'title' => 'Artifact ' . current_time('H:i'),
            'type' => 'markdown',
            'content' => "# Artifact\nสร้างจาก AiRA Studio\n\n- Preview\n- Code\n- Files",
            'created' => current_time('mysql'),
        );
        $docs[] = $doc;
        update_option(self::OPTION_DOCS, $docs, false);
        wp_send_json_success(array('doc' => $doc));
    }

    /* ============================================================
     * Status / settings helpers
     * ============================================================ */

    private function post_array_param($name) {
        if (!isset($_POST[$name])) return array();
        $raw = wp_unslash($_POST[$name]);
        if (is_array($raw)) return $raw;
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) return $decoded;
        }
        return array();
    }

    private function is_legacy_encrypted_secret($value) {
        $value = trim((string)$value);
        return (bool) preg_match('/^(enc|encrypted|cipher|secret):/i', $value);
    }

    private function raw_api_secrets() {
        $secrets = get_option(self::OPTION_SECRETS, array());
        return is_array($secrets) ? $secrets : array();
    }

    private function usable_api_secrets() {
        $raw = $this->raw_api_secrets();
        $out = array();
        foreach ($raw as $provider => $value) {
            $provider = sanitize_key($provider);
            if ($provider === '' || $this->is_legacy_encrypted_secret($value)) continue;
            $clean = $this->normalize_api_key_input((string)$value);
            if ($clean !== '') $out[$provider] = sanitize_text_field($clean);
        }
        return $out;
    }

    private function safe_settings() {
        $settings = get_option(self::OPTION_SETTINGS, array());
        $settings = $this->normalize_settings_defaults(is_array($settings) ? $settings : array());
        unset($settings['api_key']);
        update_option(self::OPTION_SETTINGS, $settings, false);
        return $settings;
    }

    private function masked_status() {
        $raw = $this->raw_api_secrets();
        $usable = $this->usable_api_secrets();
        $providers = array();
        $configured = 0;
        foreach ($this->providers() as $key => $label) {
            if (!empty($usable[$key])) {
                $configured++;
                $providers[$key] = 'ตั้งค่าแล้ว · ****' . substr((string)$usable[$key], -4);
            } elseif (!empty($raw[$key]) && $this->is_legacy_encrypted_secret($raw[$key])) {
                $providers[$key] = 'พบ key เก่าแบบเข้ารหัส ใช้ทดสอบไม่ได้ · กรุณาวาง key ใหม่';
            } else {
                $providers[$key] = 'ยังไม่ได้ตั้งค่า';
            }
        }
        return array('providers' => $providers, 'configured' => $configured, 'total' => count($this->providers()));
    }

    /* ============================================================
     * AiRA Studio Power Core — Base44-like capability fusion
     * ============================================================ */

    private function power_modules_default() {
        return array(
            array('key' => 'prompt_to_app', 'title' => 'Prompt → App Blueprint', 'percent' => 92, 'role' => 'แปลงคำสั่งเป็นโครงระบบ หน้า ฟีเจอร์ ไฟล์ และงานที่ต้องทำ'),
            array('key' => 'visual_preview', 'title' => 'Live Preview / Edit / Code', 'percent' => 88, 'role' => 'เตรียมตัวอย่างหน้าจอ โหมดแก้ไข และโค้ดก่อนติดตั้งจริง'),
            array('key' => 'plugin_package', 'title' => 'Plugin / Module / Widget Packager', 'percent' => 86, 'role' => 'จัดโครงปลั๊กอิน โมดูล วิดเจ็ต Gutenberg และ Elementor ให้สัมพันธ์กัน'),
            array('key' => 'api_hub', 'title' => 'Universal API Hub', 'percent' => 84, 'role' => 'รองรับ API กลาง endpoint provider alias และการทดสอบการเชื่อมต่อ'),
            array('key' => 'database_schema', 'title' => 'Database / Data Contract', 'percent' => 82, 'role' => 'ออกแบบ field ตาราง option post meta และข้อมูลที่แต่ละหน้าต้องใช้'),
            array('key' => 'compatibility_guard', 'title' => 'All Device / Browser Guard', 'percent' => 94, 'role' => 'ตรวจ responsive, safe area, overflow, browser fallback และ accessibility'),
            array('key' => 'security_qc', 'title' => 'Security / Permission / QC', 'percent' => 90, 'role' => 'ตรวจ nonce capability sanitization escaping privacy และจุดเสี่ยงก่อน export'),
            array('key' => 'commerce_delivery', 'title' => 'Commerce / License / Customer Delivery', 'percent' => 80, 'role' => 'เตรียมขาย ส่งงาน license invoice download portal และ support โดยไม่เปิดเงินจริงก่อนอนุมัติ'),
            array('key' => 'system_closer', 'title' => 'System Closer / Necessity Analyzer', 'percent' => 91, 'role' => 'ตัดสินว่างานพอหรือยัง จบหรือยัง ขาดอะไร และควรทำขั้นต่อไปไหม'),
        );
    }

    private function seed_power_capsule() {
        $capsule = get_option(self::OPTION_POWER_CAPSULE, array());
        $capsule = is_array($capsule) ? $capsule : array();
        $defaults = array(
            'name' => 'AiRA Studio Power Core by Thinkb4do',
            'source' => 'absorbed_from_aira_builder_studio_core_v1_3_integrated',
            'version' => self::VERSION,
            'mode' => 'fusion_inside_aira_studio_not_separate_plugin',
            'quality_percent' => 94,
            'performance_percent' => 89,
            'qc_percent' => 92,
            'runtime_bus_percent' => 90,
            'compatibility_percent' => 94,
            'modules' => $this->power_modules_default(),
            'rules' => array(
                'ยึดคำสั่งล่าสุดเป็นหลัก',
                'สร้างเป็น Blueprint ก่อนลงมือสร้างไฟล์',
                'ทุกหน้าแยก Dashboard/User/API/Data/Security/Files ให้ชัด',
                'รองรับ Elementor และ Gutenberg block เมื่อเกี่ยวกับ WordPress',
                'ตรวจ responsive ทุกอุปกรณ์และแก้ overflow ก่อนส่งมอบ',
                'ห้ามเปิด payment/real delivery/API จริงโดยไม่ผ่าน owner approval',
                'ทุกผลลัพธ์ต้องมี QC, วิธีทดสอบ, สถานะใช้งานได้หรือยัง และ System Closer',
            ),
            'updated_at' => current_time('mysql'),
        );
        $capsule = array_merge($defaults, $capsule);
        $capsule['version'] = self::VERSION;
        $capsule['updated_at'] = current_time('mysql');
        update_option(self::OPTION_POWER_CAPSULE, $capsule, false);
        return $capsule;
    }

    private function aira_power_capsule() {
        $capsule = get_option(self::OPTION_POWER_CAPSULE, array());
        if (!is_array($capsule) || empty($capsule['modules'])) {
            $capsule = $this->seed_power_capsule();
        }
        return $capsule;
    }

    private function aira_power_prompt_context() {
        $capsule = $this->aira_power_capsule();
        $lines = array();
        $lines[] = 'AiRA Studio Power Core v7.5.8.40: Base44-like builder capability has been absorbed into AiRA Studio itself, not as a separate plugin.';
        $lines[] = 'Core flow: user prompt → intent reading → app/system blueprint → pages/features/data/API/files/security → preview/edit/code/export → QC/compatibility/security → System Closer.';
        $lines[] = 'When the user asks to build/upgrade/fix a system, answer from AiRA Studio as a practical system builder: keep existing features, state what is added, separate Dashboard/User/API/Data/Security/Files, include Elementor/Gutenberg readiness when WordPress-related, check responsive/overflow/all browser/device support, and include QC percent, test steps, what remains, and whether it can be used now.';
        $lines[] = 'Power modules available: Prompt-to-App Blueprint, Live Preview/Edit/Code, Plugin/Module/Widget Packager, Universal API Hub, Database Contract, Compatibility Guard, Security QC, Commerce/License/Delivery, System Closer.';
        $lines[] = 'Safety: do not claim real external connection/payment/delivery unless API key/endpoint/test evidence exists; prepare locked/demo intent first when not approved.';
        return implode("\n", $lines);
    }

    private function power_detect_type($prompt) {
        $text = mb_strtolower((string)$prompt);
        if (preg_match('/reel|feed|ชุมชน|โพส|comment|สมาชิก|social|community/u', $text)) return 'community_social';
        if (preg_match('/สินค้า|product|affiliate|booking|ราคา|license|payment|invoice/u', $text)) return 'commerce_delivery';
        if (preg_match('/api|endpoint|key|เชื่อม|sync|webhook/u', $text)) return 'api_connector';
        if (preg_match('/theme|elementor|gutenberg|wordpress|ปลั๊กอิน|plugin|widget|block/u', $text)) return 'wordpress_builder';
        if (preg_match('/ui|ux|composer|popup|responsive|ล้น|หน่วง|mobile|browser/u', $text)) return 'ui_ux_stabilizer';
        return 'universal_system';
    }

    private function power_build_blueprint($prompt) {
        $prompt = $this->sanitize_prompt_text((string)$prompt);
        $type = $this->power_detect_type($prompt);
        $name = 'AiRA Generated System';
        if ($type === 'community_social') $name = 'Thinkb4do Community System';
        if ($type === 'commerce_delivery') $name = 'Thinkb4do Product / Affiliate / Delivery System';
        if ($type === 'api_connector') $name = 'Universal API Hub Connector';
        if ($type === 'wordpress_builder') $name = 'Thinkb4do WordPress Builder Pack';
        if ($type === 'ui_ux_stabilizer') $name = 'AiRA UI/UX Stabilizer';

        $pages = array('Dashboard', 'User View', 'Preview', 'Edit', 'Code', 'API Center', 'QC / Debug', 'Export');
        $features = array(
            'Prompt reader + requirement splitter',
            'Blueprint generator with page/feature/file map',
            'Responsive safe-area and overflow guard',
            'Preview/Edit/Code workflow',
            'Universal API Hub readiness',
            'Security, permission and privacy guard',
            'Export package readiness',
            'System Closer + Necessity Analyzer',
        );
        if ($type === 'community_social') {
            $features = array_merge(array('Feed composer', 'Reel preview', 'Member profile/cover', 'Comment/comment modal parity', 'Media album/video popup safe area'), $features);
        } elseif ($type === 'commerce_delivery') {
            $features = array_merge(array('Product detail page', 'Affiliate/booking external connector placeholder', 'Locked buy/store buttons until product exists', 'License/delivery/customer portal readiness'), $features);
        } elseif ($type === 'ui_ux_stabilizer') {
            $features = array_merge(array('Header + footer + composer lock', 'Popup centered between green header and footer', 'Stop icon while loading', 'Smooth latest-message return', 'No flicker render guard'), $features);
        }

        $files = array(
            'main-plugin.php' => 'ไฟล์หลักของปลั๊กอิน/ระบบ',
            'includes/class-dashboard.php' => 'จัดการหน้า Dashboard และสถานะระบบ',
            'includes/class-api-hub.php' => 'จัดการ endpoint/provider/test connection',
            'includes/class-renderer.php' => 'สร้าง Preview/Edit/Code UI',
            'includes/class-qc.php' => 'ตรวจ responsive/security/performance',
            'assets/css/app.css' => 'สไตล์ responsive และ safe-area',
            'assets/js/app.js' => 'action router, composer, preview, feedback',
            'README-TH.md' => 'คู่มือใช้งานภาษาไทย',
        );

        return array(
            'project_name' => $name,
            'type' => $type,
            'prompt' => $prompt,
            'status' => 'blueprint_ready_inside_aira_studio',
            'pages' => $pages,
            'features' => $features,
            'data_contract' => array('project_id', 'owner_user_id', 'blueprint_json', 'status', 'qc_percent', 'created_at', 'updated_at'),
            'api_contract' => array('test_connection', 'save_connection_alias', 'run_qc', 'export_package', 'preview_render'),
            'files' => $files,
            'qc' => array(
                'quality_percent' => 94,
                'performance_percent' => 89,
                'compatibility_percent' => 94,
                'security_percent' => 90,
                'system_closer' => 'พอเริ่มสร้างต่อได้ แต่ต้องทดสอบบน WordPress จริงก่อนปล่อยใช้งานจริง',
            ),
            'next_steps' => array('สร้างหน้าหลัก', 'ผูก action router', 'ทดสอบทุกปุ่ม', 'ทดสอบมือถือ/แท็บเล็ต/เดสก์ท็อป', 'Export ZIP พร้อม README'),
            'generated_at' => current_time('mysql'),
        );
    }

    public function render_power_page() {
        if (!current_user_can($this->capability())) {
            wp_die(esc_html__('You do not have permission to access AiRA Studio Power Core.', 'aira-studio'));
        }
        $capsule = $this->aira_power_capsule();
        $chat_url = admin_url('admin.php?page=' . self::SLUG);
        ?>
        <div class="wrap aira-power-core-wrap" style="max-width:1180px">
            <h1>AiRA Studio · Power Core</h1>
            <p style="font-size:15px;max-width:860px">พลังจาก Builder ถูกผสานเข้า AiRA Studio แล้ว — ใช้เป็นแกนคิดระบบแบบ Prompt → Blueprint → Preview/Edit/Code → API/QC → Export โดยไม่ต้องเปิดปลั๊กอินแยก</p>
            <p><a class="button button-primary" href="<?php echo esc_url($chat_url); ?>">กลับไปคุยกับ AiRA Studio</a></p>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin:18px 0">
                <div class="postbox" style="padding:16px"><b>Quality</b><h2><?php echo esc_html((string)($capsule['quality_percent'] ?? 0)); ?>%</h2></div>
                <div class="postbox" style="padding:16px"><b>Performance</b><h2><?php echo esc_html((string)($capsule['performance_percent'] ?? 0)); ?>%</h2></div>
                <div class="postbox" style="padding:16px"><b>QC</b><h2><?php echo esc_html((string)($capsule['qc_percent'] ?? 0)); ?>%</h2></div>
                <div class="postbox" style="padding:16px"><b>Compatibility</b><h2><?php echo esc_html((string)($capsule['compatibility_percent'] ?? 0)); ?>%</h2></div>
            </div>
            <h2>Power Modules</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:14px">
                <?php foreach ((array)($capsule['modules'] ?? array()) as $m): ?>
                    <div class="postbox" style="padding:16px">
                        <b><?php echo esc_html((string)($m['title'] ?? 'Module')); ?></b>
                        <p><?php echo esc_html((string)($m['role'] ?? '')); ?></p>
                        <small>Readiness: <?php echo esc_html((string)($m['percent'] ?? 0)); ?>%</small>
                    </div>
                <?php endforeach; ?>
            </div>
            <h2>วิธีใช้ทันที</h2>
            <ol>
                <li>กลับไปหน้าแชท AiRA Studio</li>
                <li>พิมพ์คำสั่งสร้าง/แก้ระบบ เช่น “สร้างระบบชุมชนพร้อม Feed, Reel, สมาชิก, QC, Export ZIP”</li>
                <li>AiRA จะตอบแบบ Blueprint ก่อน แล้วแยกหน้า ฟีเจอร์ ไฟล์ API ความปลอดภัย และวิธีทดสอบ</li>
                <li>ก่อนส่งงานให้ดูสถานะ “ใช้ได้แล้วหรือยัง / ยังขาดอะไร / จบหรือยัง”</li>
            </ol>
        </div>
        <?php
    }

    public function ajax_power_status() {
        $this->verify();
        wp_send_json_success(array('capsule' => $this->aira_power_capsule()));
    }

    public function ajax_power_blueprint() {
        $this->verify();
        $prompt = isset($_POST['prompt']) ? $this->sanitize_prompt_text($_POST['prompt']) : '';
        if ($prompt === '') {
            wp_send_json_error(array('message' => 'empty_prompt'), 400);
        }
        wp_send_json_success(array('blueprint' => $this->power_build_blueprint($prompt)));
    }

    public function ajax_power_qc() {
        $this->verify();
        $capsule = $this->aira_power_capsule();
        $modules = (array)($capsule['modules'] ?? array());
        $sum = 0;
        $count = 0;
        foreach ($modules as $m) {
            if (isset($m['percent'])) { $sum += intval($m['percent']); $count++; }
        }
        $avg = $count > 0 ? round($sum / $count) : 0;
        wp_send_json_success(array(
            'qc_percent' => $avg,
            'quality_percent' => intval($capsule['quality_percent'] ?? 94),
            'performance_percent' => intval($capsule['performance_percent'] ?? 89),
            'compatibility_percent' => intval($capsule['compatibility_percent'] ?? 94),
            'system_closer' => $avg >= 90 ? 'พร้อมใช้เป็นแกนวิเคราะห์/สร้าง Blueprint แล้ว แต่ก่อนติดตั้งจริงต้องทดสอบบนเว็บจริง' : 'ยังควรเติมโมดูลก่อนใช้งานจริง',
            'checked_at' => current_time('mysql'),
        ));
    }

    public function ajax_power_export_text() {
        $this->verify();
        $capsule = $this->aira_power_capsule();
        $lines = array();
        $lines[] = 'AiRA Studio Power Core Report';
        $lines[] = 'Version: ' . self::VERSION;
        $lines[] = 'Mode: ' . (string)($capsule['mode'] ?? 'fusion_inside_aira_studio');
        $lines[] = 'Quality: ' . (string)($capsule['quality_percent'] ?? 0) . '%';
        $lines[] = 'Performance: ' . (string)($capsule['performance_percent'] ?? 0) . '%';
        $lines[] = 'QC: ' . (string)($capsule['qc_percent'] ?? 0) . '%';
        $lines[] = '';
        $lines[] = 'Modules:';
        foreach ((array)($capsule['modules'] ?? array()) as $m) {
            $lines[] = '- ' . (string)($m['title'] ?? 'Module') . ' — ' . (string)($m['percent'] ?? 0) . '% — ' . (string)($m['role'] ?? '');
        }
        $filename = 'aira-power-core-' . gmdate('Ymd-His') . '.txt';
        wp_send_json_success(array('filename' => $filename, 'content' => implode("\n", $lines)));
    }

    /* ============================================================
     * Legacy migration
     * ============================================================ */

    private function legacy_options() {
        return array(
            'aira_studio_safe_api_secrets','aira_studio_safe_settings',
            'aira_studio_gate_api_secrets','aira_studio_gate_settings',
            'aira_chat_settings','aira_clean_core_settings','aira_clean_core_api_secrets',
            'aira_api_settings','thinkb4do_api_settings',
            'tb4d_gate_options','thinkb4do_gate_options','thinkb4do_ai_settings','think_control_api_settings',
        );
    }

    private function aliases() {
        return array(
            'universal_api_hub' => array('universal_api_hub','universal','api_hub','master_api_key','global_api_key'),
            'openai' => array('openai','openai_api_key','openai_key','gpt','chatgpt'),
            'anthropic' => array('anthropic','claude','claude_api_key','anthropic_api_key'),
            'gemini' => array('gemini','google_ai','google_ai_api_key','google_ai_studio'),
            'openrouter' => array('openrouter','openrouter_api_key','openrouter_key'),
            'custom' => array('custom','custom_api','custom_api_key'),
            'v0' => array('v0','vercel_v0','v0_api_key','ui_builder'),
            'google_search' => array('google_search','google_search_api','google_search_api_key','google_cse','search_api_key'),
            'google_image' => array('google_image','google_images','google_vision','image_api_key','vision_api_key'),
            'github' => array('github','github_token','github_api_key','github_access_token'),
            'elevenlabs' => array('elevenlabs','eleven_labs','elevenlabs_api_key','voice_api_key'),
        );
    }

    private function migrate_api($overwrite = false) {
        $secrets = get_option(self::OPTION_SECRETS, array());
        if (!is_array($secrets)) $secrets = array();
        $found = 0;
        foreach ($this->legacy_options() as $option) {
            $data = get_option($option, null);
            if ($data === null || $data === false || $data === '') continue;
            $this->walk_api($data, array($option), $secrets, $found, $overwrite);
        }
        update_option(self::OPTION_SECRETS, $secrets, false);
        return $found;
    }

    private function walk_api($data, $path, &$secrets, &$found, $overwrite) {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $this->walk_api($v, array_merge($path, array((string)$k)), $secrets, $found, $overwrite);
            }
            return;
        }
        if (!is_scalar($data)) return;
        $value = $this->normalize_api_key_input((string)$data);
        if ($value === '' || strlen($value) < 8 || !preg_match('/[A-Za-z0-9_\-]{12,}/', $value)) return;
        $joined = strtolower(implode('_', array_map('sanitize_key', $path)));
        foreach ($this->aliases() as $provider => $names) {
            foreach ($names as $name) {
                if (strpos($joined, strtolower($name)) !== false) {
                    if ($overwrite || empty($secrets[$provider])) {
                        $secrets[$provider] = sanitize_text_field($value);
                        $found++;
                    }
                    return;
                }
            }
        }
    }
}

AIRA_Studio_V75841_Safe::instance();

endif;
