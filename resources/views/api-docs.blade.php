<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            display: inline-block;
            background: #90c53f;
            color: #000;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.75em;
            font-weight: 600;
            margin-left: 5px;
        }

        .topbar-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .authorize-btn {
            background: #1db584;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .authorize-btn:hover {
            background: #17a06f;
        }

        .loading-spinner {
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #1db584;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 300px 1fr;
            min-height: calc(100vh - 80px);
        }

        .sidebar {
            background: #fafafa;
            border-right: 1px solid #e0e0e0;
            overflow-y: auto;
            padding: 20px;
            max-height: calc(100vh - 120px);
        }

        .servers-section {
            margin-bottom: 30px;
        }

        .servers-label {
            font-size: 0.85em;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 10px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .server-select {
            width: 100%;
            background: #fff;
            border: 1px solid #e0e0e0;
            color: #3e3e42;
            padding: 8px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
        }

        .server-select:hover {
            border-color: #90c53f;
        }

        .docs-label {
            font-size: 0.85em;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 15px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .group-title {
            font-size: 0.85em;
            text-transform: uppercase;
            color: #999;
            margin-bottom: 10px;
            margin-top: 20px;
            padding-left: 10px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .endpoint-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 5px;
            color: #3e3e42;
            background: #fff;
            text-decoration: none;
            font-size: 0.9em;
            border: none;
            width: 100%;
            text-align: left;
        }

        .endpoint-item:hover {
            background: #e8e8e8;
        }

        .endpoint-item.active {
            background: #e0e0e0;
            border-left: 3px solid #90c53f;
            padding-left: 7px;
        }

        .method-badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-weight: 600;
            font-size: 0.75em;
            color: white;
            min-width: 40px;
            text-align: center;
            flex-shrink: 0;
        }

        .method-badge.get { background: #61affe; }
        .method-badge.post { background: #49cc90; }
        .method-badge.put { background: #fca130; }
        .method-badge.patch { background: #50e3c2; }
        .method-badge.delete { background: #f93e3e; }

        .endpoint-path {
            font-family: 'Courier New', monospace;
            font-size: 0.8em;
            color: #666;
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .main-content {
            padding: 0;
            overflow-y: auto;
            max-height: calc(100vh - 120px);
        }

        .endpoint-detail {
            padding: 40px;
        }

        .detail-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .detail-method-path {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }

        .detail-method-badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 600;
            color: white;
            font-size: 0.9em;
        }

        .detail-method-badge.get { background: #61affe; }
        .detail-method-badge.post { background: #49cc90; }
        .detail-method-badge.put { background: #fca130; }
        .detail-method-badge.patch { background: #50e3c2; }
        .detail-method-badge.delete { background: #f93e3e; }

        .detail-path {
            font-family: 'Courier New', monospace;
            font-size: 1.3em;
            color: #3e3e42;
            word-break: break-all;
        }

        .detail-description {
            color: #666;
            margin-top: 10px;
            font-size: 0.95em;
        }

        .section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 0.95em;
            font-weight: 700;
            color: #3e3e42;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .expandable {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
        }

        .expandable-header {
            padding: 15px;
            background: #fafafa;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 500;
            color: #3e3e42;
            border-bottom: 1px solid #e0e0e0;
            user-select: none;
            transition: all 0.2s ease;
        }

        .expandable-header:hover {
            background: #f5f5f5;
        }

        .expandable-header.collapsed {
            border-bottom: none;
        }

        .toggle-icon {
            transition: transform 0.3s ease;
            font-size: 0.8em;
        }

        .expandable-header:not(.collapsed) .toggle-icon {
            transform: rotate(180deg);
        }

        .expandable-content {
            max-height: 500px;
            overflow-y: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: #fafafa;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 1px solid #e0e0e0;
            color: #3e3e42;
            font-size: 0.85em;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            color: #3e3e42;
            font-size: 0.95em;
        }

        .param-name {
            font-family: 'Courier New', monospace;
            color: #e91e63;
            font-weight: 600;
            font-size: 0.95em;
        }

        .param-type {
            font-family: 'Courier New', monospace;
            color: #2196f3;
            font-weight: 500;
        }

        .code-block {
            background: #1e1e1e;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 15px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.85em;
            color: #d4d4d4;
            max-height: 400px;
            overflow-y: auto;
        }

        .code-block pre {
            margin: 0;
        }

        .request-tester {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #3e3e42;
            font-size: 0.95em;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            background: #fff;
            color: #3e3e42;
            border: 1px solid #e0e0e0;
            padding: 10px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #90c53f;
            box-shadow: 0 0 0 3px rgba(144, 197, 63, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.95em;
        }

        .btn-primary {
            background: #4a90e2;
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background: #357abd;
        }

        .btn-primary:disabled {
            background: #ccc;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .btn-success {
            background: #49cc90;
            color: white;
        }

        .btn-success:hover:not(:disabled) {
            background: #3db870;
        }

        .btn-success:disabled {
            background: #ccc;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .response-container {
            margin-top: 20px;
        }

        .response-status {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-weight: 600;
            background: #fff;
            border: 1px solid #e0e0e0;
        }

        .response-status.success {
            background: #e8f5e9;
            border-color: #49cc90;
            color: #2e7d32;
        }

        .response-status.error {
            background: #ffebee;
            border-color: #f93e3e;
            color: #c62828;
        }

        .saved-responses-list {
            margin-top: 20px;
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
        }

        .saved-response-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            background: #fafafa;
        }

        .saved-response-item:hover {
            background: #f5f5f5;
        }

        .saved-response-time {
            font-size: 0.85em;
            color: #999;
        }

        .btn-view-response {
            background: #2196f3;
            color: white;
            padding: 5px 12px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 0.8em;
            transition: all 0.3s ease;
        }

        .btn-view-response:hover {
            background: #1976d2;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-state-icon {
            font-size: 3em;
            margin-bottom: 10px;
        }

        @media (max-width: 1024px) {
            .container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                display: none;
            }
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
                        <div class="api-title">{{ apiData.title }}</div>
                        <div class="api-version">
                            Version {{ apiData.version }}
                            <span class="version-badge">OAS 3.0</span>
                        </div>
                    </div>=
                </div>
                <div class="topbar-actions">
                    <div v-if="loading" class="loading-spinner"></div>
                    <button v-else class="authorize-btn" @click="showAuthDialog">🔓 Authorize</button>
                </div>
            </div>
        </div>

        <div class="container">
            <!-- Sidebar -->
            <div class="sidebar">
                <div class="servers-section">
                    <div class="servers-label">Servers</div>
                    <select class="server-select" v-model="selectedServer">
                        <option value="">{{ baseUrl }}</option>
                    </select>
                </div>

                <div class="docs-label">Endpoints</div>
                <div v-if="loading" class="empty-state">
                    <div class="loading-spinner"></div>
                    <p>Loading endpoints...</p>
                </div>
                <div v-else-if="groupedRoutes.length === 0" class="empty-state">
                    <p>No endpoints found</p>
                </div>
                <template v-else>
                    <template v-for="group in groupedRoutes" :key="group.name">
                        <div class="group-title">{{ group.name }}</div>
                        <button
                            v-for="(route, index) in group.routes"
                            :key="index"
                            @click="selectEndpoint(route)"
                            class="endpoint-item"
                            :class="{ active: selectedRoute && selectedRoute.uri === route.uri && selectedRoute.method === route.method }"
                        >
                            <span class="method-badge" :class="route.method.toLowerCase()">{{ route.method }}</span>
                            <span class="endpoint-path" :title="route.uri">{{ route.uri }}</span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <div v-if="!selectedRoute" class="endpoint-detail">
                    <div class="empty-state">
                        <div class="empty-state-icon">🚀</div>
                        <p>Select an endpoint from the sidebar</p>
                    </div>
                </div>

                <div v-else class="endpoint-detail">
                    <div class="detail-header">
                        <div class="detail-method-path">
                            <span class="detail-method-badge" :class="selectedRoute.method.toLowerCase()">{{ selectedRoute.method }}</span>
                            <span class="detail-path">{{ selectedRoute.uri }}</span>
                        </div>
                        <div class="detail-description">
                            {{ selectedRoute.description }}
                            <span v-if="selectedRoute.requires_auth" style="display: inline-block; background: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 3px; font-size: 0.8em; margin-left: 10px; border: 1px solid #ffc107;">🔒 Requires Auth</span>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-title">Response</div>
                        <div class="expandable">
                            <div class="expandable-header" @click="toggleResponse" :class="{ collapsed: responseCollapsed }">
                                <span>200 - OK</span>
                                <span class="toggle-icon">▼</span>
                            </div>
                            <div v-if="!responseCollapsed" class="expandable-content">
                                <div style="padding: 15px;">
                                    <div style="font-weight: 500; margin-bottom: 10px;">application/json</div>
                                    <div class="code-block"><pre>{{ JSON.stringify(selectedRoute.response_example, null, 2) }}</pre></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-title">Try It Out</div>
                        <div class="request-tester">
                            <div class="form-group">
                                <label>Request Body (JSON)</label>
                                <textarea v-model="requestBody" placeholder="Enter JSON request body"></textarea>
                            </div>

                            <div class="button-group">
                                <button class="btn btn-primary" @click="sendRequest" :disabled="sending">
                                    {{ sending ? 'Sending...' : 'Send Request' }}
                                </button>
                                <button class="btn btn-success" @click="saveCurrentResponse" :disabled="!lastResponse">
                                    Save Response
                                </button>
                            </div>

                            <div v-if="lastResponse" class="response-container">
                                <div class="response-status" :class="lastResponseStatus">
                                    {{ lastResponseMessage }}
                                </div>
                                <div class="code-block"><pre>{{ JSON.stringify(lastResponse, null, 2) }}</pre></div>
                            </div>

                            <div v-if="savedResponses.length > 0" style="margin-top: 20px;">
                                <div class="section-title">Saved Responses</div>
                                <div class="saved-responses-list">
                                    <div v-for="(resp, idx) in savedResponses" :key="idx" class="saved-response-item">
                                        <span class="saved-response-time">{{ formatDate(resp.timestamp) }}</span>
                                        <button class="btn-view-response" @click="viewSavedResponse(resp)">View</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const { createApp, ref, computed, reactive } = Vue;

        createApp({
            setup() {
                // Reactive state
                const loading = ref(true);
                const sending = ref(false);
                const apiData = reactive({ title: 'Loading...', version: '1.0.0', routes: [] });
                const selectedRoute = ref(null);
                const selectedServer = ref('');
                const requestBody = ref('{}');
                const lastResponse = ref(null);
                const lastResponseMessage = ref('');
                const lastResponseStatus = ref('');
                const savedResponses = ref([]);
                const responseCollapsed = ref(false);
                const baseUrl = ref('');

                // Computed
                const groupedRoutes = computed(() => {
                    const groups = {};
                    apiData.routes.forEach(route => {
                        const parts = route.uri.split('/').filter(p => p);
                        const prefix = parts[0] || 'General';
                        if (!groups[prefix]) groups[prefix] = [];
                        groups[prefix].push(route);
                    });

                    return Object.entries(groups).map(([name, routes]) => ({
                        name: name.charAt(0).toUpperCase() + name.slice(1),
                        routes
                    }));
                });

                // Methods
                const fetchApiData = async () => {
                    try {
                        const response = await fetch('/api/docs/fetch');
                        const data = await response.json();
                        apiData.title = data.title;
                        apiData.version = data.version;
                        apiData.routes = data.routes;
                        baseUrl.value = window.location.origin;
                        selectedServer.value = window.location.origin;
                    } catch (error) {
                        console.error('Error fetching API data:', error);
                        apiData.title = 'Error Loading API';
                    } finally {
                        loading.value = false;
                    }
                };

                const selectEndpoint = (route) => {
                    selectedRoute.value = route;
                    requestBody.value = JSON.stringify(route.request_example || {}, null, 2);
                    lastResponse.value = null;
                    loadSavedResponses(route);
                };

                const sendRequest = async () => {
                    if (!selectedRoute.value) return;

                    sending.value = true;
                    try {
                        const body = JSON.parse(requestBody.value);
                        const response = await fetch('/api/test-request', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({
                                method: selectedRoute.value.method,
                                uri: selectedRoute.value.uri,
                                body,
                            }),
                        });

                        const data = await response.json();
                        lastResponse.value = data;
                        lastResponseStatus.value = response.ok ? 'success' : 'error';
                        lastResponseMessage.value = `✓ Status: ${response.status} ${response.statusText}`;
                    } catch (error) {
                        lastResponse.value = null;
                        lastResponseStatus.value = 'error';
                        lastResponseMessage.value = `✗ Error: ${error.message}`;
                    } finally {
                        sending.value = false;
                    }
                };

                const saveCurrentResponse = async () => {
                    if (!lastResponse.value || !selectedRoute.value) return;

                    try {
                        const response = await fetch('/api/save-response', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({
                                route_uri: selectedRoute.value.uri,
                                route_method: selectedRoute.value.method,
                                response_data: lastResponse.value,
                                timestamp: new Date().toISOString(),
                            }),
                        });

                        if (response.ok) {
                            alert('Response saved successfully!');
                            loadSavedResponses(selectedRoute.value);
                        } else {
                            alert('Failed to save response');
                        }
                    } catch (error) {
                        alert(`Error saving response: ${error.message}`);
                    }
                };

                const loadSavedResponses = async (route) => {
                    try {
                        const response = await fetch(
                            `/api/saved-responses?uri=${encodeURIComponent(route.uri)}&method=${encodeURIComponent(route.method)}`
                        );
                        const data = await response.json();
                        savedResponses.value = data.responses || [];
                    } catch (error) {
                        console.error('Error loading saved responses:', error);
                        savedResponses.value = [];
                    }
                };

                const viewSavedResponse = (response) => {
                    alert(JSON.stringify(response.data, null, 2));
                };

                const toggleResponse = () => {
                    responseCollapsed.value = !responseCollapsed.value;
                };

                const showAuthDialog = () => {
                    alert('Authorization feature coming soon!');
                };

                const formatDate = (dateString) => {
                    return new Date(dateString).toLocaleString();
                };

                // Lifecycle
                fetchApiData();

                return {
                    loading,
                    sending,
                    apiData,
                    selectedRoute,
                    selectedServer,
                    requestBody,
                    lastResponse,
                    lastResponseMessage,
                    lastResponseStatus,
                    savedResponses,
                    responseCollapsed,
                    groupedRoutes,
                    baseUrl,
                    selectEndpoint,
                    sendRequest,
                    saveCurrentResponse,
                    viewSavedResponse,
                    toggleResponse,
                    showAuthDialog,
                    formatDate,
                    JSON,
                };
            },
        }).mount('#app');
    </script>
</body>
</html>
