/**
 * Laravel API Inspector - Vue.js 3 Application
 * Real-time API documentation viewer with interactive testing
 */

const { createApp, ref, computed, reactive, onMounted } = Vue;

export default {
    name: 'ApiDocsApp',
    setup() {
        // State
        const loading = ref(true);
        const sending = ref(false);
        const apiData = ref({ routes: [], title: 'API Documentation', version: '1.0.0' });
        const selectedRoute = ref(null);
        const requestBody = ref('{}');
        const lastResponse = ref(null);
        const savedResponses = ref([]);
        const expandedResponses = reactive({});

        // Computed
        const groupedRoutes = computed(() => {
            if (!apiData.value.routes) return {};

            return apiData.value.routes.reduce((groups, route) => {
                // Extract prefix from URI (e.g., /api/users -> /api)
                const parts = route.uri.split('/').filter(p => p);
                const prefix = parts.length > 0 ? `/${parts[0]}` : 'Other';

                if (!groups[prefix]) {
                    groups[prefix] = [];
                }
                groups[prefix].push(route);
                return groups;
            }, {});
        });

        // Methods
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
                        method: `${selectedRoute.value.method}`,
                        uri: `${selectedRoute.value.uri}`,
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
                    `/api/saved-responses?method=${encodeURIComponent(selectedRoute.value.method)}&route_uri=${encodeURIComponent(selectedRoute.value.uri)}`
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

        // Lifecycle
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
};
