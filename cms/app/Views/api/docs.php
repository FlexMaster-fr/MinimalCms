<div class="container">
    <div class="page-header">
        <h1><?= $this->lang('api.search_documentation') ?></h1>
    </div>
    
    <div class="api-documentation">
        <section class="api-section">
            <h2><?= $this->lang('api.search_endpoint') ?></h2>
            <div class="endpoint">
                <code class="method">GET</code>
                <code class="url">/api/search?q={query}&language={language}&license={license}&stars={stars}&forks={forks}&sort={sort}&direction={direction}&page={page}&limit={limit}</code>
            </div>
            
            <div class="parameters">
                <h3><?= $this->lang('api.parameters') ?></h3>
                <table class="parameters-table">
                    <thead>
                        <tr>
                            <th><?= $this->lang('api.parameter') ?></th>
                            <th><?= $this->lang('api.type') ?></th>
                            <th><?= $this->lang('api.description') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>q</td>
                            <td>string</td>
                            <td><?= $this->lang('api.query_description') ?></td>
                        </tr>
                        <tr>
                            <td>language</td>
                            <td>string</td>
                            <td><?= $this->lang('api.language_description') ?></td>
                        </tr>
                        <tr>
                            <td>license</td>
                            <td>string</td>
                            <td><?= $this->lang('api.license_description') ?></td>
                        </tr>
                        <tr>
                            <td>location</td>
                            <td>string</td>
                            <td><?= $this->lang('api.location_description') ?></td>
                        </tr>
                        <tr>
                            <td>stars</td>
                            <td>integer</td>
                            <td><?= $this->lang('api.stars_description') ?></td>
                        </tr>
                        <tr>
                            <td>forks</td>
                            <td>integer</td>
                            <td><?= $this->lang('api.forks_description') ?></td>
                        </tr>
                        <tr>
                            <td>sort</td>
                            <td>string</td>
                            <td><?= $this->lang('api.sort_description') ?></td>
                        </tr>
                        <tr>
                            <td>direction</td>
                            <td>string</td>
                            <td><?= $this->lang('api.direction_description') ?></td>
                        </tr>
                        <tr>
                            <td>page</td>
                            <td>integer</td>
                            <td><?= $this->lang('api.page_description') ?></td>
                        </tr>
                        <tr>
                            <td>limit</td>
                            <td>integer</td>
                            <td><?= $this->lang('api.limit_description') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="response">
                <h3><?= $this->lang('api.response_format') ?></h3>
                <pre><code>{
  "status": "success",
  "query": "example",
  "repositories": {
    "items": [ ... ],
    "total": 123,
    "pages": 7
  },
  "organizations": {
    "items": [ ... ],
    "total": 45,
    "pages": 3
  },
  "users": {
    "items": [ ... ],
    "total": 67,
    "pages": 4
  },
  "filters": {
    "languages": [ ... ],
    "licenses": [ ... ],
    "locations": [ ... ]
  },
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 235,
    "pages": 12
  }
}</code></pre>
            </div>
        </section>
        
        <section class="api-section">
            <h2><?= $this->lang('api.repository_endpoint') ?></h2>
            <div class="endpoint">
                <code class="method">GET</code>
                <code class="url">/api/repository/{owner}/{repo}</code>
            </div>
            
            <div class="response">
                <h3><?= $this->lang('api.response_format') ?></h3>
                <pre><code>{
  "status": "success",
  "repository": {
    "id": 123,
    "github_id": 45678,
    "name": "repository-name",
    "full_name": "owner/repository-name",
    "description": "Repository description",
    "language": "PHP",
    "license": "MIT",
    "forks_count": 123,
    "stargazers_count": 456,
    "watchers_count": 78,
    "created_at": "2023-01-15T12:34:56Z",
    "updated_at": "2023-05-20T10:11:12Z",
    "topics": [ ... ]
  }
}</code></pre>
            </div>
        </section>
        
        <section class="api-section">
            <h2><?= $this->lang('api.organization_endpoint') ?></h2>
            <div class="endpoint">
                <code class="method">GET</code>
                <code class="url">/api/organization/{org}</code>
            </div>
            
            <div class="response">
                <h3><?= $this->lang('api.response_format') ?></h3>
                <pre><code>{
  "status": "success",
  "organization": {
    "id": 123,
    "github_id": 45678,
    "login": "organization-name",
    "name": "Organization Name",
    "description": "Organization description",
    "url": "https://github.com/organization-name",
    "avatar_url": "https://...",
    "location": "San Francisco, CA",
    "public_repos": 123,
    "followers": 456,
    "following": 0,
    "created_at": "2020-01-15T12:34:56Z",
    "updated_at": "2023-05-20T10:11:12Z"
  },
  "repositories": [ ... ]
}</code></pre>
            </div>
        </section>
        
        <section class="api-section">
            <h2><?= $this->lang('api.user_endpoint') ?></h2>
            <div class="endpoint">
                <code class="method">GET</code>
                <code class="url">/api/user/{username}</code>
            </div>
            
            <div class="response">
                <h3><?= $this->lang('api.response_format') ?></h3>
                <pre><code>{
  "status": "success",
  "user": {
    "id": 123,
    "github_id": 45678,
    "login": "username",
    "name": "User Name",
    "company": "Company Name",
    "blog": "https://example.com",
    "location": "New York, NY",
    "email": "user@example.com",
    "bio": "User bio",
    "avatar_url": "https://...",
    "url": "https://github.com/username",
    "public_repos": 123,
    "public_gists": 45,
    "followers": 678,
    "following": 90,
    "created_at": "2019-01-15T12:34:56Z",
    "updated_at": "2023-05-20T10:11:12Z"
  },
  "repositories": [ ... ]
}</code></pre>
            </div>
        </section>
    </div>
</div>

<style>
.api-documentation {
    margin-top: 30px;
    margin-bottom: 50px;
}

.api-section {
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 1px solid #eaecef;
}

.endpoint {
    background-color: #f6f8fa;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-family: SFMono-Regular, Consolas, "Liberation Mono", Menlo, monospace;
}

.method {
    background-color: #0366d6;
    color: white;
    padding: 5px 8px;
    border-radius: 4px;
    margin-right: 10px;
}

.url {
    color: #24292e;
    word-break: break-all;
}

.parameters-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.parameters-table th,
.parameters-table td {
    border: 1px solid #e1e4e8;
    padding: 8px 12px;
    text-align: left;
}

.parameters-table th {
    background-color: #f6f8fa;
}

pre {
    background-color: #f6f8fa;
    padding: 16px;
    border-radius: 6px;
    overflow: auto;
}

pre code {
    font-family: SFMono-Regular, Consolas, "Liberation Mono", Menlo, monospace;
    font-size: 14px;
    line-height: 1.45;
    display: block;
    white-space: pre;
}
</style>