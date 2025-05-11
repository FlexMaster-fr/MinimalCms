<?php
/**
 * This view is primarily for documentation purposes.
 * The actual search is handled via API endpoints that return JSON.
 */
?>

<div class="api-documentation">
    <h1><?= $this->e($this->lang('api.search_documentation')) ?></h1>
    
    <section class="api-section">
        <h2><?= $this->e($this->lang('api.search_endpoint')) ?></h2>
        <pre><code>GET /api/search</code></pre>
        
        <h3><?= $this->e($this->lang('api.parameters')) ?></h3>
        <table class="api-params">
            <thead>
                <tr>
                    <th><?= $this->e($this->lang('api.parameter')) ?></th>
                    <th><?= $this->e($this->lang('api.type')) ?></th>
                    <th><?= $this->e($this->lang('api.description')) ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>q</td>
                    <td>string</td>
                    <td><?= $this->e($this->lang('api.query_description')) ?></td>
                </tr>
                <tr>
                    <td>language</td>
                    <td>string</td>
                    <td><?= $this->e($this->lang('api.language_description')) ?></td>
                </tr>
                <tr>
                    <td>license</td>
                    <td>string</td>
                    <td><?= $this->e($this->lang('api.license_description')) ?></td>
                </tr>
                <tr>
                    <td>location</td>
                    <td>string</td>
                    <td><?= $this->e($this->lang('api.location_description')) ?></td>
                </tr>
                <tr>
                    <td>stars</td>
                    <td>integer</td>
                    <td><?= $this->e($this->lang('api.stars_description')) ?></td>
                </tr>
                <tr>
                    <td>forks</td>
                    <td>integer</td>
                    <td><?= $this->e($this->lang('api.forks_description')) ?></td>
                </tr>
                <tr>
                    <td>sort</td>
                    <td>string</td>
                    <td><?= $this->e($this->lang('api.sort_description')) ?></td>
                </tr>
                <tr>
                    <td>direction</td>
                    <td>string</td>
                    <td><?= $this->e($this->lang('api.direction_description')) ?></td>
                </tr>
                <tr>
                    <td>page</td>
                    <td>integer</td>
                    <td><?= $this->e($this->lang('api.page_description')) ?></td>
                </tr>
                <tr>
                    <td>limit</td>
                    <td>integer</td>
                    <td><?= $this->e($this->lang('api.limit_description')) ?></td>
                </tr>
            </tbody>
        </table>
        
        <h3><?= $this->e($this->lang('api.response_format')) ?></h3>
        <pre><code>{
  "status": "success",
  "query": "search term",
  "repositories": {
    "items": [ /* array of repository objects */ ],
    "total": 123,
    "pages": 7
  },
  "organizations": {
    "items": [ /* array of organization objects */ ],
    "total": 45,
    "pages": 3
  },
  "users": {
    "items": [ /* array of user objects */ ],
    "total": 67,
    "pages": 4
  },
  "filters": {
    "languages": [ /* array of available languages */ ],
    "licenses": [ /* array of available licenses */ ],
    "locations": [ /* array of available locations */ ]
  },
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 235,
    "pages": 12
  }
}</code></pre>
    </section>
    
    <section class="api-section">
        <h2><?= $this->e($this->lang('api.repository_endpoint')) ?></h2>
        <pre><code>GET /api/repository/{owner}/{repo}</code></pre>
        
        <h3><?= $this->e($this->lang('api.response_format')) ?></h3>
        <pre><code>{
  "status": "success",
  "repository": {
    "id": 123,
    "github_id": 456789,
    "name": "repo-name",
    "full_name": "owner/repo-name",
    "owner_login": "owner",
    "description": "Repository description",
    "language_name": "PHP",
    "topics": ["web", "mvc", "php"],
    /* other repository data */
  }
}</code></pre>
    </section>
    
    <section class="api-section">
        <h2><?= $this->e($this->lang('api.organization_endpoint')) ?></h2>
        <pre><code>GET /api/organization/{org}</code></pre>
        
        <h3><?= $this->e($this->lang('api.response_format')) ?></h3>
        <pre><code>{
  "status": "success",
  "organization": {
    "id": 123,
    "github_id": 456789,
    "login": "org-name",
    "name": "Organization Name",
    "description": "Organization description",
    /* other organization data */
  },
  "repositories": [
    /* array of repository objects */
  ]
}</code></pre>
    </section>
    
    <section class="api-section">
        <h2><?= $this->e($this->lang('api.user_endpoint')) ?></h2>
        <pre><code>GET /api/user/{username}</code></pre>
        
        <h3><?= $this->e($this->lang('api.response_format')) ?></h3>
        <pre><code>{
  "status": "success",
  "user": {
    "id": 123,
    "github_id": 456789,
    "login": "username",
    "name": "User Name",
    "bio": "User bio",
    /* other user data */
  },
  "repositories": [
    /* array of repository objects */
  ]
}</code></pre>
    </section>
</div>
