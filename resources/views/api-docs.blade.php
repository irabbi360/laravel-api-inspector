<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $lapiScriptVariables['title'] }} -- {{ $lapiScriptVariables['app_name'] }}</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: #fafafa;
            color: #3e3e42;
            line-height: 1.6;
        }

        #app {
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: auto;
        }

        /* Top Bar */
        .topbar {
            background: #1e1e1e;
            padding: 20px;
            border-bottom: 1px solid #3e3e42;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .api-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .api-title {
            color: #fff;
            font-size: 1.5em;
            font-weight: 500;
        }

        .api-version {
            color: #999;
            font-size: 0.9em;
        }

        .version-badge {
            background: #0066cc;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.75em;
            margin-left: 10px;
        }

        .topbar-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .loading-spinner {
            width: 20px;
            height: 20px;
            border: 3px solid #ccc;
            border-top-color: #0066cc;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Main Layout */
        .main-container {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: 320px;
            background: #252526;
            border-right: 1px solid #3e3e42;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 20px 0;
            flex-shrink: 0;
            max-height: 100%;
        }

        .sidebar::-webkit-scrollbar {
            width: 8px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: #252526;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #555;
            border-radius: 4px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #666;
        }

        .sidebar-group {
            margin-bottom: 0;
        }

        .sidebar-group-title {
            color: #999;
            font-size: 0.85em;
            font-weight: 600;
            padding: 10px 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sidebar-route {
            padding: 10px 20px;
            cursor: pointer;
            border-left: 3px solid transparent;
            transition: background 0.2s, border-color 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #ccc;
        }

        .sidebar-route:hover {
            background: #333;
        }

        .sidebar-route.active {
            background: #0066cc;
            border-left-color: #0066cc;
            color: white;
        }

        .route-method-badge {
            font-size: 0.7em;
            font-weight: 600;
            padding: 3px 6px;
            border-radius: 3px;
            min-width: 40px;
            text-align: center;
            text-transform: uppercase;
        }

        .route-method-badge.get {
            background: #61affe;
            color: white;
        }

        .route-method-badge.post {
            background: #49cc90;
            color: white;
        }

        .route-method-badge.put {
            background: #fca130;
            color: white;
        }

        .route-method-badge.delete {
            background: #f93e3e;
            color: white;
        }

        .route-method-badge.patch {
            background: #50e3c2;
            color: white;
        }

        .route-path {
            font-family: 'Courier New', monospace;
            font-size: 0.85em;
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Content Area */
        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #999;
        }

        .empty-state-icon {
            font-size: 4em;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .endpoint-detail {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .detail-header {
            padding: 30px;
            background: white;
            border-bottom: 1px solid #e0e0e0;
            flex-shrink: 0;
        }

        .detail-method-path {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .detail-method-badge {
            font-size: 0.85em;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 3px;
            text-transform: uppercase;
            min-width: 50px;
            text-align: center;
        }

        .detail-method-badge.get { background: #61affe; color: white; }
        .detail-method-badge.post { background: #49cc90; color: white; }
        .detail-method-badge.put { background: #fca130; color: white; }
        .detail-method-badge.delete { background: #f93e3e; color: white; }
        .detail-method-badge.patch { background: #50e3c2; color: white; }

        .detail-path {
            font-family: 'Courier New', monospace;
            font-size: 1.1em;
            font-weight: 600;
            color: #3e3e42;
        }

        .detail-description {
            color: #666;
            font-size: 0.95em;
            margin-bottom: 10px;
        }

        .detail-body {
            flex: 1;
            overflow-y: auto;
            padding: 0;
            display: flex;
            gap: 0;
        }

        .detail-body-left {
            flex: 1;
            overflow-y: auto;
            padding: 30px;
            border-right: 1px solid #e0e0e0;
            background: #fff;
        }

        .detail-body-right {
            flex: 1;
            overflow-y: auto;
            padding: 30px;
            background: #f9f9f9;
        }

        .section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 1.1em;
            font-weight: 600;
            margin-bottom: 15px;
            color: #1e1e1e;
            border-bottom: 2px solid #0066cc;
            padding-bottom: 10px;
        }

        .expandable {
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
        }

        .expandable-header {
            background: #f5f5f5;
            padding: 15px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            user-select: none;
        }

        .expandable-header:hover {
            background: #efefef;
        }

        .toggle-icon {
            transition: transform 0.2s;
            color: #999;
        }

        .expandable.active .toggle-icon {
            transform: rotate(180deg);
        }

        .expandable-content {
            display: none;
            padding: 15px;
            background: white;
            border-top: 1px solid #e0e0e0;
        }

        .expandable.active .expandable-content {
            display: block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9em;
        }

        thead {
            background: #fafafa;
        }

        th {
            text-align: left;
            padding: 12px;
            font-weight: 600;
            color: #666;
            border-bottom: 1px solid #e0e0e0;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
        }

        tr:hover {
            background: #fafafa;
        }

        .param-name {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: #0066cc;
        }

        .param-type {
            font-family: 'Courier New', monospace;
            color: #666;
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
        }

        .request-tester {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 20px;
        }

        .tester-tabs {
            display: flex;
            gap: 0;
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 20px;
        }

        .tab-btn {
            flex: 1;
            padding: 12px 16px;
            border: none;
            background: #f5f5f5;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: #666;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
        }

        .tab-btn:hover {
            background: #e8e8e8;
        }

        .tab-btn.active {
            background: #fff;
            color: #333;
            border-bottom-color: #2196F3;
        }

        .form-fields-container {
            display: grid;
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #3e3e42;
        }

        .form-field-group {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
        }

        .field-label {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            font-weight: 600;
            color: #3e3e42;
        }

        .field-name {
            font-family: 'Courier New', monospace;
            color: #0066cc;
            font-weight: 700;
        }

        .field-required {
            display: inline-block;
            background: #ffebee;
            color: #c62828;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.75em;
            font-weight: 600;
            border: 1px solid #ef5350;
        }

        .field-optional {
            display: inline-block;
            color: #999;
            font-size: 0.85em;
            font-weight: 500;
        }

        .field-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            margin-bottom: 8px;
        }

        .field-input:focus {
            outline: none;
            border-color: #0066cc;
            box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
        }

        .field-description {
            font-size: 12px;
            color: #888;
            font-style: italic;
            margin-bottom: 5px;
        }

        .field-type {
            font-size: 12px;
            color: #666;
        }

        .json-editor-container {
            margin-bottom: 20px;
        }

        .json-editor-container label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group textarea {
            width: 100%;
            height: auto;
            padding: 10px;
            font-family: monospace;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .json-editor {
            width: 100%;
            height: auto;
            padding: 10px;
            font-family: monospace;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
        }

        .json-editor:focus {
            outline: none;
            border-color: #2196F3;
            box-shadow: 0 0 4px rgba(33, 150, 243, 0.2);
        }

        .field-description {
            display: block;
            color: #666;
            font-size: 0.85em;
            margin-bottom: 4px;
        }

        /* Form Fields Info Display (Read-only) */
        .form-fields-info {
            display: grid;
            gap: 12px;
        }

        .form-field-info {
            padding: 12px;
            background: #f5f5f5;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
        }

        .field-label-info {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 13px;
        }

        .field-name-info {
            color: #2196F3;
            font-family: monospace;
            font-weight: 700;
        }

        .field-required-badge {
            background: #ffebee;
            color: #c62828;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 700;
        }

        .field-optional-badge {
            background: #e3f2fd;
            color: #1565c0;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 700;
        }

        .field-meta-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 12px;
            color: #666;
        }

        .field-type-info {
            display: block;
        }

        .field-example-info {
            display: block;
            font-family: monospace;
            background: #fff;
            padding: 4px 6px;
            border-radius: 2px;
        }

        .field-desc-info {
            display: block;
            font-style: italic;
            color: #888;
        }

        .field-type {
            display: block;
            color: #999;
            font-size: 0.85em;
        }

        textarea, input {
            width: 100%;
            padding: 10px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            resize: vertical;
        }

        textarea:focus, input:focus {
            outline: none;
            border-color: #0066cc;
            box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
        }

        .btn {
            background: #0066cc;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.2s;
        }

        .btn:hover:not(:disabled) {
            background: #0052a3;
        }

        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .response-section {
            background: #f5f5f5;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 15px;
            margin-top: 20px;
        }

        .response-status {
            font-weight: 600;
            margin-bottom: 15px;
        }

        .response-status.success {
            color: #27ae60;
        }

        .response-status.error {
            color: #e74c3c;
        }

        .response-code {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 15px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.85em;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .saved-responses {
            margin-top: 20px;
        }

        .saved-response-item {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .saved-response-item:hover {
            background: #fafafa;
        }

        .saved-response-time {
            color: #999;
            font-size: 0.85em;
        }

        .no-data {
            color: #999;
            font-style: italic;
        }

        .auth-badge {
            display: inline-block;
            background: #fff3cd;
            color: #856404;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 0.8em;
            margin-left: 10px;
            border: 1px solid #ffc107;
        }

        /* Auth Token Input */
        .auth-input-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .auth-input-group input {
            min-height: auto;
            min-width: 250px;
            padding: 8px 12px;
            font-size: 12px;
            background: #2a2a2a;
            border: 1px solid #444;
            color: #ccc;
        }

        .auth-input-group input:focus {
            border-color: #0066cc;
            box-shadow: none;
        }

        .auth-input-group input::placeholder {
            color: #666;
        }

        .auth-input-group label {
            color: #999;
            font-size: 12px;
            margin: 0;
            white-space: nowrap;
        }

        .auth-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #999;
        }

        .auth-status.active {
            color: #49cc90;
        }

        .auth-status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #999;
        }

        .auth-status.active .auth-status-dot {
            background: #49cc90;
        }
    </style>
</head>
<body>
    <div id="app">
        <app-root></app-root>
    </div>

    <script>
        window.apiInspector = @json($lapiScriptVariables);

        const { createApp, defineComponent, ref, computed, reactive, onMounted, watch } = Vue;

        // ============ COMPONENTS ============

        // Topbar Component
        const Topbar = defineComponent({
            name: 'Topbar',
            props: {
                apiData: Object,
                loading: Boolean,
                authToken: String,
            },
            emits: ['refresh', 'update:authToken'],
            template: `
                <div class="topbar">
                    <div class="topbar-content">
                        <div class="api-info">
                            <div>
                                <div class="api-title">@{{ apiInspector?.title || 'Laravel API Inspector' }}</div>
                                <div class="api-version">
                                    Version <span class="version-badge">@{{ apiInspector?.version || '1.0.0' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="topbar-actions">
                            <div class="auth-input-group">
                                <label>🔐 Bearer Token:</label>
                                <input
                                    :value="authToken"
                                    @input="$emit('update:authToken', $event.target.value)"
                                    type="password"
                                    placeholder="Enter auth token..."
                                />
                                <div :class="['auth-status', { active: authToken }]">
                                    <span class="auth-status-dot"></span>
                                    @{{ authToken ? 'Authenticated' : 'No token' }}
                                </div>
                            </div>
                            <div v-if="loading" class="loading-spinner"></div>
                            <button v-else @click="$emit('refresh')" class="btn" style="background: #666;">↻ Refresh</button>
                            <a href="https://github.com/irabbi360/laravel-api-inspector/issues/new" target="_blank" class="btn" style="background: #666;">Feature Request</a>
                        </div>
                    </div>
                </div>
            `,
        });

        // Sidebar Component
        const Sidebar = defineComponent({
            name: 'Sidebar',
            props: {
                groupedRoutes: Object,
                selectedRoute: Object,
            },
            emits: ['select-endpoint'],
            template: `
                <div class="sidebar">
                    <div v-for="(routes, group) in groupedRoutes" :key="group" class="sidebar-group">
                        <div class="sidebar-group-title">@{{ group }}</div>
                        <div
                            v-for="route in routes"
                            :key="route.method + '-' + route.uri"
                            @click="$emit('select-endpoint', route)"
                            :class="['sidebar-route', { active: selectedRoute && selectedRoute.method === route.method && selectedRoute.uri === route.uri }]"
                        >
                            <span class="route-method-badge" :class="route.method.toLowerCase()">
                                @{{ route.method.substring(0, 3) }}
                            </span>
                            <span class="route-path">@{{ route.uri }}</span>
                        </div>
                    </div>
                </div>
            `,
        });

        // Parameters Component
        const ParametersSection = defineComponent({
            name: 'ParametersSection',
            props: {
                parameters: Object,
            },
            template: `
                <div v-if="parameters && Object.keys(parameters).length > 0" class="section">
                    <div class="section-title">Parameters</div>
                    <div class="expandable">
                        <div class="expandable-header">
                            <span>Path Parameters</span>
                            <span class="toggle-icon">▼</span>
                        </div>
                        <div class="expandable-content">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(param, name) in parameters" :key="name">
                                        <td><span class="param-name">@{{ name }}</span></td>
                                        <td><span class="param-type">@{{ param.type || 'string' }}</span></td>
                                        <td>@{{ param.description || '' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `,
        });

        // Request Body Component
        const RequestBodySection = defineComponent({
            name: 'RequestBodySection',
            props: {
                requestRules: Object,
            },
            template: `
                <div v-if="requestRules && Object.keys(requestRules).length > 0" class="section">
                    <div class="section-title">Request Body Parameters</div>
                    <div class="form-fields-info">
                        <div v-for="(field, fieldName) in requestRules" :key="fieldName" class="form-field-info">
                            <label class="field-label-info">
                                <span class="field-name-info">@{{ fieldName }}</span>
                                <span v-if="field.required" class="field-required-badge">required</span>
                                <span v-else class="field-optional-badge">optional</span>
                            </label>
                            <div class="field-meta-info">
                                <span v-if="field.type" class="field-type-info">
                                    <strong>Type:</strong> @{{ field.type }}
                                </span>
                                <span v-if="field.example" class="field-example-info">
                                    <strong>Example:</strong> @{{ field.example }}
                                </span>
                                <span v-if="field.description" class="field-desc-info">
                                    @{{ field.description }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `,
        });

        // Request Tester Component
        const RequestTester = defineComponent({
            name: 'RequestTester',
            props: {
                requestBody: String,
                requestRules: Object,
                sending: Boolean,
                route: Object,
                pathParams: Object,
            },
            emits: ['update:requestBody', 'send-request', 'update:pathParams'],
            data() {
                return {
                    formData: {},
                    useJsonEditor: true,
                };
            },
            computed: {
                hasRequestRules() {
                    return this.requestRules && Object.keys(this.requestRules).length > 0;
                },
                extractedPathParams() {
                    if (!this.route || !this.route.uri) return {};
                    const matches = this.route.uri.match(/{([^}]+)}/g);
                    if (!matches) return {};
                    const params = {};
                    matches.forEach(match => {
                        const paramName = match.replace(/{|}/g, '');
                        params[paramName] = this.pathParams[paramName] || '';
                    });
                    return params;
                },
            },
            watch: {
                requestRules: {
                    handler(newRules) {
                        if (newRules) {
                            // Initialize form data when rules change
                            this.formData = {};
                            Object.keys(newRules).forEach(field => {
                                this.formData[field] = '';
                            });
                            this.updateRequestBody();
                        }
                    },
                    immediate: true,
                },
                formData: {
                    handler() {
                        if (!this.useJsonEditor) {
                            this.updateRequestBody();
                        }
                    },
                    deep: true,
                },
            },
            methods: {
                updateRequestBody() {
                    const body = {};
                    Object.keys(this.formData).forEach(key => {
                        const value = this.formData[key];
                        // Try to parse as JSON if it looks like JSON
                        if (value.toString().trim().startsWith('{') || value.toString().trim().startsWith('[')) {
                            try {
                                body[key] = JSON.parse(value);
                            } catch {
                                body[key] = value;
                            }
                        } else {
                            body[key] = value;
                        }
                    });
                    this.$emit('update:requestBody', JSON.stringify(body, null, 2));
                },
                getInputType(field) {
                    if (!this.requestRules || !this.requestRules[field]) return 'text';
                    const type = this.requestRules[field].type || 'string';
                    if (type === 'integer' || type === 'number') return 'number';
                    if (type === 'boolean') return 'checkbox';
                    return 'text';
                },
            },
            template: `
                <div class="section">
                    <div class="section-title">Send Request</div>
                    <div class="request-tester">
                        <div v-if="Object.keys(extractedPathParams).length > 0" style="margin-bottom: 20px; padding: 15px; background: #f0f7ff; border: 1px solid #b3d9ff; border-radius: 4px;">
                            <div style="font-weight: 600; margin-bottom: 12px; color: #0066cc;">📋 Path Parameters</div>
                            <div class="form-fields-container">
                                <div v-for="(value, paramName) in extractedPathParams" :key="paramName" class="form-field-group" style="margin-bottom: 12px;">
                                    <label class="field-label">
                                        <span class="field-name">@{{ paramName }}</span>
                                        <span class="field-required">required</span>
                                    </label>
                                    <input
                                        :value="pathParams[paramName] || ''"
                                        @input="$emit('update:pathParams', { ...pathParams, [paramName]: $event.target.value })"
                                        type="text"
                                        :placeholder="'Enter ' + paramName"
                                        class="field-input"
                                    />
                                </div>
                            </div>
                        </div>

                        <div v-if="hasRequestRules" class="tester-tabs">
                            <button
                                @click="useJsonEditor = true"
                                :class="['tab-btn', { active: useJsonEditor }]"
                            >
                                <></> JSON Editor
                            </button>
                            <button
                                @click="useJsonEditor = false"
                                :class="['tab-btn', { active: !useJsonEditor }]"
                            >
                                📝 Form Fields
                            </button>
                        </div>

                        <div v-if="hasRequestRules && !useJsonEditor" class="form-fields-container">
                            <div v-for="(field, fieldName) in requestRules" :key="fieldName" class="form-field-group">
                                <label class="field-label">
                                    <span class="field-name">@{{ fieldName }}</span>
                                    <span v-if="field.required" class="field-required">required</span>
                                    <span v-else class="field-optional">optional</span>
                                </label>
                                <input
                                    v-model="formData[fieldName]"
                                    :type="getInputType(fieldName)"
                                    :placeholder="field.example"
                                    class="field-input"
                                />
                                <span v-if="field.description" class="field-description">@{{ field.description }}</span>
                                <span v-if="field.type" class="field-type">Type: <strong>@{{ field.type }}</strong></span>
                            </div>
                        </div>

                        <div v-if="hasRequestRules && useJsonEditor" class="json-editor-container">
                            <label>Request Body (JSON)</label>
                            <textarea
                                :value="requestBody"
                                @input="$emit('update:requestBody', $event.target.value)"
                                placeholder="Enter JSON request body"
                                class="json-editor"
                            ></textarea>
                        </div>

                        <div v-if="!hasRequestRules" class="form-group">
                            <label>Request Body (JSON)</label>
                            <textarea
                                :value="requestBody"
                                @input="$emit('update:requestBody', $event.target.value)"
                                placeholder="Enter JSON request body"
                                class="json-editor"
                            ></textarea>
                        </div>

                        <button @click="$emit('send-request')" :disabled="sending" class="btn btn-send">
                            @{{ sending ? 'Sending...' : 'Send Request' }}
                        </button>
                    </div>
                </div>
            `,
        });

        // Response Schema Component
        const ResponseSchema = defineComponent({
            name: 'ResponseSchema',
            props: {
                schema: Object,
            },
            template: `
                <div v-if="schema && schema.schema && Object.keys(schema.schema).length > 0" class="section">
                    <div class="section-title">Response Schema</div>
                    <div style="background: white; border: 1px solid #e0e0e0; border-radius: 4px; padding: 15px;">
                        <div style="margin-bottom: 15px; color: #666; font-size: 0.9em;">
                            <strong>Resource:</strong> <span style="font-family: monospace; color: #0066cc;">@{{ schema.resource_class }}</span>
                        </div>
                        <pre style="background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px; line-height: 1.6; color: #333;"><code>@{{ formatSchema(schema.schema) }}</code></pre>
                    </div>
                </div>
            `,
            methods: {
                formatSchema(schema) {
                    // Convert schema object to JSON format with proper indentation
                    const obj = {};
                    Object.keys(schema).forEach(key => {
                        const field = schema[key];
                        if (typeof field === 'object' && field !== null) {
                            obj[key] = field;
                        } else {
                            obj[key] = field;
                        }
                    });
                    return JSON.stringify(obj, null, 2);
                },
            },
        });

        // Response Viewer Component
        const ResponseViewer = defineComponent({
            name: 'ResponseViewer',
            props: {
                response: Object,
                schema: Object,
            },
            emits: ['save-response'],
            data() {
                return {
                    showHeaders: false,
                };
            },
            template: `
                <div v-if="response" class="section">
                    <div class="section-title">Response</div>
                    <div class="response-section">
                        <div class="response-status" :class="response.status >= 200 && response.status < 300 ? 'success' : 'error'">
                            Status: @{{ response.status }} @{{ response.statusText || '' }}
                        </div>

                        <div style="margin: 15px 0; border-top: 1px solid #ddd; padding-top: 15px;">
                            <button
                                @click="showHeaders = !showHeaders"
                                style="background: #666; margin-bottom: 10px;"
                                class="btn"
                            >
                                @{{ showHeaders ? '▼' : '▶' }} Response Headers
                            </button>

                            <div v-if="showHeaders" style="background: #f5f5f5; padding: 10px; border-radius: 4px; margin-bottom: 15px; max-height: 200px; overflow-y: auto;">
                                <div v-if="response.headers && Object.keys(response.headers).length > 0">
                                    <div v-for="(value, header) in response.headers" :key="header" style="font-size: 12px; margin-bottom: 5px; word-break: break-all;">
                                        <strong>@{{ header }}:</strong> @{{ value }}
                                    </div>
                                </div>
                                <div v-else style="color: #999; font-size: 12px;">
                                    <p>No headers available</p>
                                    <p style="margin-top: 5px; font-size: 11px; color: #aaa;">
                                        Note: Due to browser CORS policy, some headers may not be accessible. The server can expose additional headers using the Access-Control-Expose-Headers header.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Response Body:</label>
                            <div class="response-code">
                                @{{ typeof response.body === 'string' ? response.body : JSON.stringify(response.body, null, 2) }}
                            </div>
                        </div>

                        <button @click="$emit('save-response')" class="btn" style="background: #27ae60;">💾 Save Response</button>
                    </div>
                </div>
            `,
        });

        // Saved Responses Component
        const SavedResponses = defineComponent({
            name: 'SavedResponses',
            props: {
                responses: Array,
            },
            emits: ['view-response'],
            template: `
                <div v-if="responses && responses.length > 0" class="section">
                    <div class="section-title">Saved Responses History</div>
                    <div class="saved-responses">
                        <div
                            v-for="(saved, index) in responses"
                            :key="index"
                            @click="$emit('view-response', saved.data)"
                            class="saved-response-item"
                        >
                            <div class="saved-response-time">@{{ new Date(saved.timestamp).toLocaleString() }}</div>
                            <div style="margin-top: 8px; font-size: 13px;">
                                <span style="color: #666;"><strong>Status:</strong> @{{ saved.data?.status || 'Unknown' }}</span>
                                <span v-if="saved.data?.body?.length" style="color: #999; margin-left: 15px;">
                                    <strong>Size:</strong> @{{ (JSON.stringify(saved.data.body).length / 1024).toFixed(2) }}KB
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `,
        });

        // Detail Header Component
        const DetailHeader = defineComponent({
            name: 'DetailHeader',
            props: {
                route: Object,
            },
            template: `
                <div class="detail-header">
                    <div class="detail-method-path">
                        <span class="detail-method-badge" :class="route?.method?.toLowerCase()">
                            @{{ route?.method }}
                        </span>
                        <span class="detail-path">@{{ route?.uri }}</span>
                    </div>
                    <div class="detail-description">
                        @{{ route?.description }}
                        <span v-if="route?.requires_auth" class="auth-badge">🔒 Requires Auth</span>
                    </div>
                </div>
            `,
        });

        // Endpoint Detail Component
        const EndpointDetail = defineComponent({
            name: 'EndpointDetail',
            props: {
                route: Object,
                requestBody: String,
                lastResponse: Object,
                savedResponses: Array,
                sending: Boolean,
                pathParams: Object,
            },
            emits: ['update:requestBody', 'send-request', 'save-response', 'view-response', 'update:pathParams'],
            components: {
                DetailHeader,
                ParametersSection,
                RequestBodySection,
                RequestTester,
                ResponseSchema,
                ResponseViewer,
                SavedResponses,
            },
            template: `
                <div class="endpoint-detail">
                    <DetailHeader :route="route" />
                    <div class="detail-body">
                        <div class="detail-body-left">
                            <ParametersSection :parameters="route?.parameters" />
                            <RequestBodySection :request-rules="route?.request_rules" />
                        </div>
                        <div class="detail-body-right">
                            <RequestTester
                                :request-body="requestBody"
                                :request-rules="route?.request_rules"
                                :route="route"
                                :path-params="pathParams"
                                :sending="sending"
                                @update:requestBody="$emit('update:requestBody', $event)"
                                @update:pathParams="$emit('update:pathParams', $event)"
                                @send-request="$emit('send-request')"
                            />
                            <ResponseSchema :schema="route?.response_schema" />
                            <ResponseViewer
                                :response="lastResponse"
                                :schema="route?.response_schema"
                                @save-response="$emit('save-response')"
                            />
                            <SavedResponses
                                :responses="savedResponses"
                                @view-response="$emit('view-response', $event)"
                            />
                        </div>
                    </div>
                </div>
            `,
        });

        // Main App Root Component
        const AppRoot = defineComponent({
            name: 'AppRoot',
            components: {
                Topbar,
                Sidebar,
                EndpointDetail,
            },
            setup() {
                const loading = ref(true);
                const sending = ref(false);
                const apiData = ref({ routes: [], title: 'API Documentation', version: '1.0.0' });
                const selectedRoute = ref(null);
                const requestBody = ref('{}');
                const lastResponse = ref(null);
                const savedResponses = ref([]);
                const authToken = ref(localStorage.getItem('api-docs-auth-token') || '');
                const pathParams = ref({});

                const groupedRoutes = computed(() => {
                    if (!apiData.value.routes) return {};

                    return apiData.value.routes.reduce((groups, route) => {
                        const parts = route.uri.split('/').filter(p => p);
                        const prefix = parts.length > 0 ? `/${parts[0]}` : 'Other';

                        if (!groups[prefix]) {
                            groups[prefix] = [];
                        }
                        groups[prefix].push(route);
                        return groups;
                    }, {});
                });

                const fetchApiData = async () => {
                    try {
                        loading.value = true;
                        const response = await fetch('/api-docs/fetch');
                        apiData.value = await response.json();
                        if (apiData.value.routes.length > 0) {
                            selectedRoute.value = apiData.value.routes[0];
                            loadSavedResponses();
                        }
                    } catch (error) {
                        console.error('Failed to fetch API data:', error);
                        apiData.value = { routes: [], title: 'Error', version: '1.0.0' };
                    } finally {
                        loading.value = false;
                    }
                };

                const selectEndpoint = (route) => {
                    // Ensure we're updating with a fresh copy to trigger reactivity
                    selectedRoute.value = JSON.parse(JSON.stringify(route));
                    requestBody.value = '{}';
                    lastResponse.value = null;
                    pathParams.value = {};
                    loadSavedResponses();
                };

                const sendRequest = async () => {
                    if (!selectedRoute.value) return;

                    try {
                        sending.value = true;

                        // Build the request headers with JSON content type
                        const headers = new Headers({
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        });

                        // Add auth header if token is provided and route requires auth
                        if (authToken.value && selectedRoute.value.requires_auth) {
                            headers.append('Authorization', `Bearer ${authToken.value}`);
                        }

                        // Parse request body
                        let requestData = {};
                        let bodyToSend = undefined;
                        let finalUri = selectedRoute.value.uri;

                        try {
                            if (requestBody.value && requestBody.value.trim()) {
                                requestData = JSON.parse(requestBody.value);
                                bodyToSend = JSON.stringify(requestData);
                            }
                            if(pathParams.value){
                                // Replace path parameters in the URI for the request only
                                Object.keys(pathParams.value).forEach(param => {
                                    const value = encodeURIComponent(pathParams.value[param]);
                                    finalUri = finalUri.replace(`{${param}}`, value);
                                });
                            }
                        } catch (e) {
                            lastResponse.value = { error: 'Invalid JSON in request body', status: 400 };
                            sending.value = false;
                            return;
                        }

                        // Build the URL with base path
                        const baseUrl = window.location.origin;
                        const url = `${baseUrl}/${finalUri.replace(/^\//, '')}`;
                        
                        // Build fetch options
                        const fetchOptions = {
                            method: selectedRoute.value.method,
                            headers: headers,
                            mode: 'cors',
                            credentials: 'include',
                        };

                        // Only add body for methods that support it
                        if (!['GET', 'HEAD'].includes(selectedRoute.value.method)) {
                            fetchOptions.body = bodyToSend;
                        }

                        // Make the actual API request
                        const response = await fetch(url, fetchOptions);

                        // Capture response details
                        const responseText = await response.text();
                        let responseBody = null;

                        try {
                            responseBody = JSON.parse(responseText);
                        } catch (e) {
                            responseBody = responseText;
                        }

                        // Capture response headers
                        const responseHeaders = {};
                        response.headers.forEach((value, name) => {
                            responseHeaders[name] = value;
                        });

                        lastResponse.value = {
                            status: response.status,
                            statusText: response.statusText,
                            headers: responseHeaders,
                            body: responseBody,
                        };
                    } catch (error) {
                        lastResponse.value = {
                            error: error.message,
                            status: 0,
                            statusText: 'Network Error'
                        };
                    } finally {
                        sending.value = false;
                    }
                };

                const saveCurrentResponse = async () => {
                    if (!selectedRoute.value || !lastResponse.value) return;

                    try {
                        await fetch('/api-docs/save-response', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            },
                            body: JSON.stringify({
                                route_method: `${selectedRoute.value.method}`,
                                route_uri: `${selectedRoute.value.uri}`,
                                response: lastResponse.value,
                            }),
                        });

                        loadSavedResponses();
                    } catch (error) {
                        console.error('Failed to save response:', error);
                    }
                };

                const loadSavedResponses = async () => {
                    if (!selectedRoute.value) return;

                    try {
                        const response = await fetch(
                            `/api-docs/saved-responses?method=${encodeURIComponent(selectedRoute.value.method)}&uri=${encodeURIComponent(selectedRoute.value.uri)}`
                        );
                        const data = await response.json();
                        savedResponses.value = data.responses || [];
                    } catch (error) {
                        console.error('Failed to load saved responses:', error);
                        savedResponses.value = [];
                    }
                };

                const viewSavedResponse = (saved) => {
                    lastResponse.value = saved;
                };

                // Watch for authToken changes and save to localStorage
                watch(authToken, (newToken) => {
                    if (newToken) {
                        localStorage.setItem('api-docs-auth-token', newToken);
                    } else {
                        localStorage.removeItem('api-docs-auth-token');
                    }
                });

                onMounted(() => {
                    fetchApiData();
                });

                return {
                    loading,
                    sending,
                    apiData,
                    selectedRoute,
                    requestBody,
                    lastResponse,
                    savedResponses,
                    authToken,
                    pathParams,
                    groupedRoutes,
                    fetchApiData,
                    selectEndpoint,
                    sendRequest,
                    saveCurrentResponse,
                    loadSavedResponses,
                    viewSavedResponse,
                };
            },
            template: `
                <div>
                    <Topbar
                        :api-data="apiData"
                        :loading="loading"
                        :auth-token="authToken"
                        @refresh="fetchApiData"
                        @update:authToken="authToken = $event"
                    />
                    <div class="main-container">
                        <Sidebar
                            :grouped-routes="groupedRoutes"
                            :selected-route="selectedRoute"
                            @select-endpoint="selectEndpoint"
                        />
                        <div class="content">
                            <div v-if="!selectedRoute" class="empty-state">
                                <div class="empty-state-icon">📚</div>
                                <p>Select an endpoint to view details</p>
                            </div>
                            <EndpointDetail
                                v-else
                                :route="selectedRoute"
                                :request-body="requestBody"
                                :last-response="lastResponse"
                                :saved-responses="savedResponses"
                                :sending="sending"
                                :path-params="pathParams"
                                @update:requestBody="requestBody = $event"
                                @update:pathParams="pathParams = $event"
                                @send-request="sendRequest"
                                @save-response="saveCurrentResponse"
                                @view-response="viewSavedResponse"
                            />
                        </div>
                    </div>
                </div>
            `,
        });

        // ============ APP INITIALIZATION ============

        const app = createApp({
            components: {
                AppRoot,
            },
            template: '<AppRoot />',
        });

        app.mount('#app');
        console.log('API Inspector SPA initialized.', apiInspector);
    </script>
</body>
</html>
