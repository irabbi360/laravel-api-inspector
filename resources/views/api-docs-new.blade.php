<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>API Documentation</title>
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
            overflow: hidden;
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
            padding: 20px 0;
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
            padding: 30px;
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

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #3e3e42;
        }

        textarea, input {
            width: 100%;
            padding: 10px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            resize: vertical;
            min-height: 80px;
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
    </style>
</head>
<body>
    <div id="app">
        <!-- Top Bar -->
        <div class="topbar">
            <div class="topbar-content">
                <div class="api-info">
                    <div>
                        <div class="api-title">@{{ apiData.title }}</div>
                        <div class="api-version">
                            Version @{{ apiData.version }}
                            <span class="version-badge">OAS 3.0</span>
                        </div>
                    </div>
                </div>
                <div class="topbar-actions">
                    <div v-if="loading" class="loading-spinner"></div>
                    <button v-else @click="fetchApiData" class="btn" style="background: #666;">↻ Refresh</button>
                </div>
            </div>
        </div>

        <!-- Main Layout -->
        <div class="main-container">
            <!-- Sidebar -->
            <div class="sidebar">
                <div v-for="(routes, group) in groupedRoutes" :key="group" class="sidebar-group">
                    <div class="sidebar-group-title">@{{ group }}</div>
                    <div
                        v-for="route in routes"
                        :key="`${route.method}-${route.uri}`"
                        @click="selectEndpoint(route)"
                        :class="['sidebar-route', { active: selectedRoute && selectedRoute.uri === route.uri && selectedRoute.method === route.method }]"
                    >
                        <span class="route-method-badge" :class="route.method.toLowerCase()">
                            @{{ route.method.substring(0, 3) }}
                        </span>
                        <span class="route-path">@{{ route.uri }}</span>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="content">
                <div v-if="!selectedRoute" class="empty-state">
                    <div class="empty-state-icon">📚</div>
                    <p>Select an endpoint to view details</p>
                </div>

                <div v-else class="endpoint-detail">
                    <!-- Detail Header -->
                    <div class="detail-header">
                        <div class="detail-method-path">
                            <span class="detail-method-badge" :class="selectedRoute.method.toLowerCase()">
                                @{{ selectedRoute.method }}
                            </span>
                            <span class="detail-path">@{{ selectedRoute.uri }}</span>
                        </div>
                        <div class="detail-description">
                            @{{ selectedRoute.description }}
                            <span v-if="selectedRoute.requires_auth" style="display: inline-block; background: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 3px; font-size: 0.8em; margin-left: 10px; border: 1px solid #ffc107;">🔒 Requires Auth</span>
                        </div>
                    </div>

                    <!-- Detail Body -->
                    <div class="detail-body">
                        <!-- Parameters Section -->
                        <div v-if="selectedRoute.parameters && Object.keys(selectedRoute.parameters).length > 0" class="section">
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
                                            <tr v-for="(param, name) in selectedRoute.parameters" :key="name">
                                                <td><span class="param-name">@{{ name }}</span></td>
                                                <td><span class="param-type">@{{ param.type || 'string' }}</span></td>
                                                <td>@{{ param.description || '' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Request Rules Section -->
                        <div v-if="selectedRoute.request_rules && Object.keys(selectedRoute.request_rules).length > 0" class="section">
                            <div class="section-title">Request Body</div>
                            <div class="expandable">
                                <div class="expandable-header">
                                    <span>application/json</span>
                                    <span class="toggle-icon">▼</span>
                                </div>
                                <div class="expandable-content">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Field</th>
                                                <th>Type</th>
                                                <th>Status</th>
                                                <th>Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(field, fieldName) in selectedRoute.request_rules" :key="fieldName">
                                                <td><span class="param-name">@{{ fieldName }}</span></td>
                                                <td><span class="param-type">@{{ field.type || 'string' }}</span></td>
                                                <td>
                                                    <span v-if="field.required" style="display: inline-block; background: #ffebee; color: #c62828; padding: 3px 8px; border-radius: 3px; font-size: 0.75em; font-weight: 600; border: 1px solid #ef5350;">required</span>
                                                    <span v-else style="color: #999; font-size: 0.85em;">optional</span>
                                                </td>
                                                <td>@{{ field.example || '' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Try It Out Section -->
                        <div class="section">
                            <div class="section-title">Try It Out</div>
                            <div class="request-tester">
                                <div v-if="selectedRoute.request_rules && Object.keys(selectedRoute.request_rules).length > 0" class="form-group">
                                    <label>Request Body (JSON)</label>
                                    <textarea v-model="requestBody" placeholder="Enter JSON request body"></textarea>
                                </div>
                                <button @click="sendRequest" :disabled="sending" class="btn">
                                    @{{ sending ? 'Sending...' : 'Send Request' }}
                                </button>
                            </div>
                        </div>

                        <!-- Response Section -->
                        <div v-if="lastResponse" class="section">
                            <div class="section-title">Response</div>
                            <div class="response-section">
                                <div class="response-status" :class="lastResponse.status >= 200 && lastResponse.status < 300 ? 'success' : 'error'">
                                    Status: @{{ lastResponse.status }} @{{ lastResponse.statusText || '' }}
                                </div>
                                <div class="response-code">
                                    @{{ JSON.stringify(lastResponse, null, 2) }}
                                </div>
                                <button @click="saveCurrentResponse" class="btn" style="margin-top: 15px; background: #27ae60;">💾 Save Response</button>
                            </div>
                        </div>

                        <!-- Saved Responses -->
                        <div v-if="savedResponses && savedResponses.length > 0" class="section">
                            <div class="section-title">Saved Responses</div>
                            <div class="saved-responses">
                                <div
                                    v-for="(saved, index) in savedResponses"
                                    :key="index"
                                    @click="viewSavedResponse(saved)"
                                    class="saved-response-item"
                                >
                                    <div class="saved-response-time">@{{ new Date(saved.created_at).toLocaleString() }}</div>
                                    <div style="margin-top: 5px; color: #666;">Status: @{{ saved.response?.status || 'Unknown' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const { createApp, ref, computed, reactive, onMounted } = Vue;

        const app = createApp({
            setup() {
                const loading = ref(true);
                const sending = ref(false);
                const apiData = ref({ routes: [], title: 'API Documentation', version: '1.0.0' });
                const selectedRoute = ref(null);
                const requestBody = ref('{}');
                const lastResponse = ref(null);
                const savedResponses = ref([]);
                const expandedResponses = reactive({});

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
                        const response = await fetch('/api/docs/fetch');
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
                    selectedRoute.value = route;
                    requestBody.value = '{}';
                    lastResponse.value = null;
                    loadSavedResponses();
                };

                const sendRequest = async () => {
                    if (!selectedRoute.value) return;

                    try {
                        sending.value = true;
                        const response = await fetch('/api/test-request', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            },
                            body: JSON.stringify({
                                method: selectedRoute.value.method,
                                uri: selectedRoute.value.uri,
                                body: requestBody.value ? JSON.parse(requestBody.value) : {},
                            }),
                        });

                        lastResponse.value = await response.json();
                    } catch (error) {
                        lastResponse.value = { error: error.message, status: 0 };
                    } finally {
                        sending.value = false;
                    }
                };

                const saveCurrentResponse = async () => {
                    if (!selectedRoute.value || !lastResponse.value) return;

                    try {
                        await fetch('/api/save-response', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            },
                            body: JSON.stringify({
                                route: `${selectedRoute.value.method} ${selectedRoute.value.uri}`,
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
                            `/api/saved-responses?route=${encodeURIComponent(selectedRoute.value.method)} ${selectedRoute.value.uri}`
                        );
                        savedResponses.value = await response.json();
                    } catch (error) {
                        console.error('Failed to load saved responses:', error);
                        savedResponses.value = [];
                    }
                };

                const viewSavedResponse = (saved) => {
                    lastResponse.value = saved.response;
                };

                const toggleResponse = (index) => {
                    expandedResponses[index] = !expandedResponses[index];
                };

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
                    expandedResponses,
                    groupedRoutes,
                    fetchApiData,
                    selectEndpoint,
                    sendRequest,
                    saveCurrentResponse,
                    loadSavedResponses,
                    viewSavedResponse,
                    toggleResponse,
                };
            },
        });

        app.mount('#app');
    </script>
</body>
</html>
