<?php

namespace Irabbi360\LaravelApiInspector\Generators;

class HtmlDocsGenerator
{
    protected array $routes;

    protected string $title;

    protected string $version;

    public function __construct(array $routes, string $title = 'Laravel API', string $version = '1.0.0')
    {
        $this->routes = $routes;
        $this->title = $title;
        $this->version = $version;
    }

    /**
     * Generate HTML documentation
     */
    public function generate(): string
    {
        return $this->renderTemplate();
    }

    /**
     * Render HTML template
     */
    protected function renderTemplate(): string
    {
        $routes = $this->routes;
        $title = $this->title;
        $version = $this->version;
        $groupedRoutes = $this->groupRoutesByPrefix($routes);

        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo htmlspecialchars($title); ?></title>
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap">
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
                }

                .topbar-actions {
                    display: flex;
                    gap: 10px;
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

                .container {
                    max-width: 1400px;
                    margin: 0 auto;
                    display: grid;
                    grid-template-columns: 300px 1fr;
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
                    text-decoration: none;
                    font-size: 0.9em;
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
                    display: none;
                    padding: 40px;
                }

                .endpoint-detail.active {
                    display: block;
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

                .expandable-header.collapsed .toggle-icon {
                    transform: rotate(0deg);
                }

                .expandable-header:not(.collapsed) .toggle-icon {
                    transform: rotate(180deg);
                }

                .expandable-content {
                    display: none;
                }

                .expandable-header:not(.collapsed) + .expandable-content {
                    display: block;
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

                table tr:last-child td {
                    border-bottom: none;
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

                .param-required {
                    background: #ffebee;
                    color: #c62828;
                    padding: 3px 8px;
                    border-radius: 3px;
                    font-size: 0.75em;
                    font-weight: 600;
                    display: inline-block;
                    border: 1px solid #ef5350;
                }

                .param-optional {
                    color: #999;
                    font-size: 0.85em;
                }

                .code-block {
                    background: #1e1e1e;
                    border: 1px solid #e0e0e0;
                    border-radius: 4px;
                    padding: 15px;
                    overflow-x: auto;
                    font-family: 'Courier New', monospace;
                    font-size: 0.85em;
                    color: #ce9178;
                }

                .code-block pre {
                    margin: 0;
                    color: #d4d4d4;
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

                .btn-primary:hover {
                    background: #357abd;
                }

                .btn-success {
                    background: #49cc90;
                    color: white;
                }

                .btn-success:hover {
                    background: #3db870;
                }

                .btn-success:disabled {
                    background: #ccc;
                    cursor: not-allowed;
                }

                .response-container {
                    margin-top: 20px;
                    display: none;
                }

                .response-container.show {
                    display: block;
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

                @media (max-width: 1024px) {
                    .container {
                        grid-template-columns: 1fr;
                    }

                    .sidebar {
                        display: none;
                    }
                }

                footer {
                    text-align: center;
                    padding: 20px;
                    color: #999;
                    margin-top: 40px;
                    border-top: 1px solid #e0e0e0;
                    font-size: 0.9em;
                }
            </style>
        </head>
        <body>
            <!-- Top Bar -->
            <div class="topbar">
                <div class="topbar-content">
                    <div class="api-info">
                        <div>
                            <div class="api-title"><?php echo htmlspecialchars($title); ?></div>
                            <div class="api-version">Version <?php echo htmlspecialchars($version); ?> <span class="version-badge">OAS 3.0</span></div>
                        </div>
                    </div>
                    <div class="topbar-actions">
                        <button class="authorize-btn" onclick="showAuthDialog()">🔓 Authorize</button>
                    </div>
                </div>
            </div>

            <div class="container">
                <!-- Sidebar -->
                <div class="sidebar">
                    <div class="servers-section">
                        <div class="servers-label">Servers</div>
                        <select class="server-select" id="server-select">
                            <option value="<?php echo config('app.url'); ?>"><?php echo config('app.url'); ?></option>
                        </select>
                    </div>

                    <div class="docs-label">Endpoints</div>
                    <?php foreach ($groupedRoutes as $group => $endpoints) { ?>
                        <div class="group-title"><?php echo htmlspecialchars($group ?: 'General'); ?></div>
                        <?php foreach ($endpoints as $index => $route) { ?>
                            <a href="#endpoint-<?php echo $index; ?>" class="endpoint-item" onclick="showEndpoint(<?php echo $index; ?>); return false;">
                                <span class="method-badge <?php echo strtolower($route['method']); ?>"><?php echo htmlspecialchars($route['method']); ?></span>
                                <span class="endpoint-path" title="<?php echo htmlspecialchars($route['uri']); ?>"><?php echo htmlspecialchars($route['uri']); ?></span>
                            </a>
                        <?php } ?>
                    <?php } ?>
                </div>

                <!-- Main Content -->
                <div class="main-content">
                    <?php foreach ($routes as $index => $route) { ?>
                        <div class="endpoint-detail" id="endpoint-<?php echo $index; ?>">
                            <div class="detail-header">
                                <div class="detail-method-path">
                                    <span class="detail-method-badge <?php echo strtolower($route['method']); ?>"><?php echo htmlspecialchars($route['method']); ?></span>
                                    <span class="detail-path"><?php echo htmlspecialchars($route['uri']); ?></span>
                                </div>
                                <div class="detail-description">
                                    <?php echo htmlspecialchars($route['description'] ?? 'No description available'); ?>
                                    <?php if ($route['requires_auth'] ?? false) { ?>
                                        <span class="auth-badge">🔒 Requires Authentication</span>
                                    <?php } ?>
                                </div>
                            </div>

                            <?php if (! empty($route['parameters'])) { ?>
                                <div class="section">
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
                                                    <?php foreach ($route['parameters'] as $param) { ?>
                                                        <tr>
                                                            <td><span class="param-name"><?php echo htmlspecialchars($param['name'] ?? ''); ?></span></td>
                                                            <td><span class="param-type"><?php echo htmlspecialchars($param['type'] ?? 'string'); ?></span></td>
                                                            <td><?php echo htmlspecialchars($param['description'] ?? ''); ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if (! empty($route['request_rules'])) { ?>
                                <div class="section">
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
                                                    <?php foreach ($route['request_rules'] as $fieldName => $field) { ?>
                                                        <tr>
                                                            <td><span class="param-name"><?php echo htmlspecialchars($fieldName); ?></span></td>
                                                            <td><span class="param-type"><?php echo htmlspecialchars($field['type'] ?? 'string'); ?></span></td>
                                                            <td>
                                                                <?php if ($field['required'] ?? false) { ?>
                                                                    <span class="param-required">required</span>
                                                                <?php } else { ?>
                                                                    <span class="param-optional">optional</span>
                                                                <?php } ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($field['example'] ?? ''); ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="section">
                                <div class="section-title">Responses</div>
                                <div class="expandable">
                                    <div class="expandable-header">
                                        <span>200 - OK</span>
                                        <span class="toggle-icon">▼</span>
                                    </div>
                                    <div class="expandable-content">
                                        <div style="padding: 15px;">
                                            <div style="font-weight: 500; margin-bottom: 10px;">application/json</div>
                                            <div class="code-block"><pre><?php echo htmlspecialchars(json_encode($route['response_example'] ?? ['success' => true], JSON_PRETTY_PRINT)); ?></pre></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="section">
                                <div class="section-title">Try It Out</div>
                                <div class="request-tester">
                                    <?php if (! empty($route['request_rules'])) { ?>
                                        <div class="form-group">
                                            <label>Request Body (JSON)</label>
                                            <textarea class="request-body-input" data-route="<?php echo htmlspecialchars(json_encode($route)); ?>">
{
<?php
$requestFields = [];
                                        foreach ($route['request_rules'] as $fieldName => $field) {
                                            $example = $field['example'] ?? '';
                                            if (is_string($example)) {
                                                $requestFields[] = '  "'.$fieldName.'": "'.addslashes($example).'"';
                                            } else {
                                                $requestFields[] = '  "'.$fieldName.'": '.json_encode($example);
                                            }
                                        }
                                        echo implode(",\n", $requestFields);
                                        ?>
}
                                            </textarea>
                                        </div>
                                    <?php } ?>

                                    <div class="button-group">
                                        <button type="button" class="btn btn-primary" onclick="sendTestRequest(this)">
                                            Send Request
                                        </button>
                                        <button type="button" class="btn btn-success" onclick="saveResponse(this)" disabled>
                                            Save Response
                                        </button>
                                    </div>

                                    <div class="response-container">
                                        <div class="response-status"></div>
                                        <div class="code-block"><pre class="response-body"></pre></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <script>
                let lastResponse = null;
                let lastRoute = null;

                function showEndpoint(index) {
                    document.querySelectorAll('.endpoint-detail').forEach(el => el.classList.remove('active'));
                    document.querySelectorAll('.endpoint-item').forEach(el => el.classList.remove('active'));
                    document.getElementById('endpoint-' + index).classList.add('active');
                    document.querySelector('[href="#endpoint-' + index + '"]').classList.add('active');
                    document.querySelector('.main-content').scrollTo({ top: 0, behavior: 'smooth' });
                }

                function showAuthDialog() {
                    alert('Authorization feature coming soon!');
                }

                document.querySelectorAll('.expandable-header').forEach(header => {
                    header.addEventListener('click', function() {
                        this.classList.toggle('collapsed');
                    });
                });

                async function sendTestRequest(button) {
                    const section = button.closest('.request-tester');
                    const textarea = section.querySelector('.request-body-input');
                    const routeData = textarea ? JSON.parse(textarea.getAttribute('data-route')) : null;

                    if (!routeData) return;

                    lastRoute = routeData;
                    const responseContainer = section.querySelector('.response-container');
                    const responseStatus = section.querySelector('.response-status');
                    const responseBody = section.querySelector('.response-body');

                    button.disabled = true;
                    button.innerHTML = 'Sending...';

                    try {
                        const requestBody = textarea ? JSON.parse(textarea.value) : {};

                        const response = await fetch('/api/test-request', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({
                                method: routeData.method,
                                uri: routeData.uri,
                                body: requestBody,
                            }),
                        });

                        const data = await response.json();
                        lastResponse = data;

                        responseContainer.classList.add('show');
                        responseStatus.className = 'response-status ' + (response.ok ? 'success' : 'error');
                        responseStatus.textContent = 'Status: ' + response.status + ' ' + response.statusText;
                        responseBody.textContent = JSON.stringify(data, null, 2);

                        section.querySelector('.btn-success').disabled = false;
                    } catch (error) {
                        responseContainer.classList.add('show');
                        responseStatus.className = 'response-status error';
                        responseStatus.textContent = 'Error: ' + error.message;
                        responseBody.textContent = error.message;
                    } finally {
                        button.disabled = false;
                        button.innerHTML = 'Send Request';
                    }
                }

                async function saveResponse(button) {
                    if (!lastResponse || !lastRoute) return;

                    button.disabled = true;
                    button.innerHTML = 'Saving...';

                    try {
                        const response = await fetch('/api/save-response', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({
                                route_uri: lastRoute.uri,
                                route_method: lastRoute.method,
                                response_data: lastResponse,
                                timestamp: new Date().toISOString(),
                            }),
                        });

                        if (response.ok) {
                            alert('Response saved successfully!');
                        } else {
                            alert('Failed to save response');
                        }
                    } catch (error) {
                        alert('Error saving response: ' + error.message);
                    } finally {
                        button.disabled = false;
                        button.innerHTML = 'Save Response';
                    }
                }

                // Show first endpoint on load
                document.addEventListener('DOMContentLoaded', function() {
                    const firstEndpoint = document.querySelector('.endpoint-item');
                    if (firstEndpoint) {
                        const href = firstEndpoint.getAttribute('href');
                        const index = href.replace('#endpoint-', '');
                        showEndpoint(parseInt(index));
                    }
                });
            </script>
        </body>
        </html>
        <?php
                                                return ob_get_clean();
    }

    /**
     * Group routes by their first path segment (API version or module)
     */
    protected function groupRoutesByPrefix(array $routes): array
    {
        $grouped = [];

        foreach ($routes as $route) {
            $uri = $route['uri'];
            $parts = array_filter(explode('/', $uri));
            $prefix = reset($parts) ?: 'General';

            if (! isset($grouped[$prefix])) {
                $grouped[$prefix] = [];
            }

            $grouped[$prefix][] = $route;
        }

        return $grouped;
    }

    /**
     * Generate as HTML string
     */
    public function generateHtml(): string
    {
        return $this->generate();
    }
}
