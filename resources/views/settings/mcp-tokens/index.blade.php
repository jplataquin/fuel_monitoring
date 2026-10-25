<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('settings.index') }}" class="text-secondary text-decoration-none">Settings</a></li>
                        <li class="breadcrumb-item active text-light" aria-current="page">MCP Tokens</li>
                    </ol>
                </nav>
                <h2 class="h4 fw-bold text-light mb-0">
                    {{ __('MCP Personal Access Tokens') }}
                </h2>
            </div>
            <div>
                <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 cursor-pointer">
                    &larr; Back to Settings
                </a>
            </div>
        </div>
    </x-slot>

    <div class="container-xl py-4">
        <!-- Flash Status Message -->
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show bg-success bg-opacity-10 border border-success border-opacity-25 text-success rounded-3 mb-4" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Newly Generated Plaintext Token Banner -->
        @if (session('plainTextToken'))
            <div class="card bg-dark border-warning border-opacity-50 shadow-sm rounded-3 mb-4 overflow-hidden">
                <div class="card-header bg-warning bg-opacity-10 border-bottom border-warning border-opacity-25 py-3">
                    <div class="d-flex align-items-center gap-2 text-warning fw-bold">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <span>Token Generated: {{ session('tokenName') }}</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <p class="text-light mb-2">
                        Please copy your new Personal Access Token now. <strong>For your security, it will never be displayed again.</strong>
                    </p>
                    <div class="input-group mb-3">
                        <input type="text" id="plainTextTokenInput" class="form-control font-monospace bg-black text-warning border-secondary" value="{{ session('plainTextToken') }}" readonly>
                        <button type="button" id="copyTokenBtn" class="btn btn-warning fw-bold px-4 cursor-pointer" onclick="copyTokenToClipboard()">
                            <span id="copyIcon">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="me-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                                </svg>
                            </span>
                            <span id="copyText">Copy Token</span>
                        </button>
                    </div>

                    <div class="bg-black bg-opacity-50 p-3 rounded-2 border border-secondary border-opacity-25">
                        <small class="text-secondary d-block mb-1 text-uppercase fw-semibold tracking-wider" style="font-size: 0.75rem;">MCP Client Configuration</small>
                        <pre class="text-light mb-0 font-monospace small"><code>"fuel-monitoring": {
  "url": "{{ url('/mcp') }}",
  "headers": {
    "Authorization": "Bearer {{ session('plainTextToken') }}"
  }
}</code></pre>
                    </div>
                </div>
            </div>
        @endif

        <div class="row g-4">
            <!-- Left: Generate Token Form -->
            <div class="col-lg-4">
                <div class="card bg-dark border-secondary border-opacity-25 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-dark border-bottom border-secondary border-opacity-25 py-3">
                        <h3 class="h6 fw-bold text-light mb-0 d-flex align-items-center gap-2">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="text-primary">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Generate New Token
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('mcp-tokens.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="tokenName" class="form-label text-light small fw-semibold">Token Description / Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="tokenName" class="form-control bg-dark text-light border-secondary @error('name') is-invalid @enderror" placeholder="e.g. Claude Desktop, Cursor AI, Reporting Bot" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-secondary" style="font-size: 0.75rem;">
                                    Give this token an identifiable label indicating which client or agent will use it.
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="userSelect" class="form-label text-light small fw-semibold">Assign to User</label>
                                <select name="user_id" id="userSelect" class="form-select bg-dark text-light border-secondary cursor-pointer">
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}" {{ (old('user_id', Auth::id()) == $user->id) ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ ucfirst($user->role) }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text text-secondary" style="font-size: 0.75rem;">
                                    The MCP agent will execute queries in the context of this user.
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold text-uppercase small py-2.5 cursor-pointer">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="me-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                                </svg>
                                Generate Token
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right: Active Tokens Table -->
            <div class="col-lg-8">
                <div class="card bg-dark border-secondary border-opacity-25 shadow-sm rounded-3">
                    <div class="card-header bg-dark border-bottom border-secondary border-opacity-25 py-3 d-flex justify-content-between align-items-center">
                        <h3 class="h6 fw-bold text-light mb-0 d-flex align-items-center gap-2">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="text-success">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                            </svg>
                            Active Personal Access Tokens
                        </h3>
                        <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-25 font-monospace">
                            {{ $tokens->total() }} Total
                        </span>
                    </div>

                    <div class="card-body p-0">
                        @if ($tokens->isEmpty())
                            <div class="text-center py-5 px-3">
                                <div class="bg-secondary bg-opacity-10 p-3 rounded-circle d-inline-block text-secondary mb-3">
                                    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                                    </svg>
                                </div>
                                <h4 class="h6 fw-semibold text-light mb-1">No MCP tokens created yet</h4>
                                <p class="text-secondary small mb-0">Generate a personal access token using the form to connect AI clients to the MCP server.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-dark table-hover align-middle mb-0 border-secondary border-opacity-25">
                                    <thead class="table-dark text-secondary small text-uppercase tracking-wider">
                                        <tr>
                                            <th class="ps-4 py-3">Token Name</th>
                                            <th class="py-3">User</th>
                                            <th class="py-3">Last Used</th>
                                            <th class="py-3">Created</th>
                                            <th class="text-end pe-4 py-3">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @foreach ($tokens as $token)
                                            <tr>
                                                <td class="ps-4 py-3">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="d-inline-block rounded-circle bg-success" style="width: 8px; height: 8px;"></span>
                                                        <span class="fw-bold text-light">{{ $token->name }}</span>
                                                    </div>
                                                </td>
                                                <td class="py-3">
                                                    @if ($token->tokenable)
                                                        <span class="text-light">{{ $token->tokenable->name }}</span>
                                                        <span class="badge bg-secondary bg-opacity-25 text-secondary border border-secondary border-opacity-25 ms-1" style="font-size: 0.75rem;">
                                                            {{ ucfirst($token->tokenable->role ?? 'user') }}
                                                        </span>
                                                    @else
                                                        <span class="text-secondary fst-italic">Unknown</span>
                                                    @endif
                                                </td>
                                                <td class="py-3 text-secondary small">
                                                    @if ($token->last_used_at)
                                                        {{ $token->last_used_at->diffForHumans() }}
                                                    @else
                                                        <span class="text-muted">Never</span>
                                                    @endif
                                                </td>
                                                <td class="py-3 text-secondary small">
                                                    {{ $token->created_at ? $token->created_at->format('M d, Y') : '—' }}
                                                </td>
                                                <td class="text-end pe-4 py-3">
                                                    <form method="POST" action="{{ route('mcp-tokens.destroy', $token) }}" onsubmit="return confirm('Are you sure you want to revoke the token \'{{ $token->name }}\'? Any client using this token will immediately lose access.');" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3 cursor-pointer" title="Revoke Token">
                                                            Revoke
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if ($tokens->hasPages())
                                <div class="p-3 border-top border-secondary border-opacity-25">
                                    {{ $tokens->links() }}
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- MCP Server Connection Info Box -->
                <div class="card bg-dark border-secondary border-opacity-25 shadow-sm rounded-3 mt-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-2 text-primary">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                            </svg>
                            <h4 class="h6 fw-bold mb-0 text-light">MCP Server Details</h4>
                        </div>
                        <p class="text-secondary small mb-2">
                            The MCP HTTP server handles Model Context Protocol JSON-RPC requests to securely expose read-only asset logs, recent fuel orders, and budget dashboards.
                        </p>
                        <div class="row g-3 small">
                            <div class="col-md-6">
                                <span class="text-secondary d-block">Server URL:</span>
                                <code class="text-primary font-monospace">{{ url('/mcp') }}</code>
                            </div>
                            <div class="col-md-6">
                                <span class="text-secondary d-block">Authentication:</span>
                                <span class="text-light">HTTP Bearer Token (`Authorization: Bearer &lt;token&gt;`)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Copy to Clipboard Script and Cursor Styles -->
    <style>
        .cursor-pointer {
            cursor: pointer !important;
        }
    </style>
    <script>
        function copyTokenToClipboard() {
            const input = document.getElementById('plainTextTokenInput');
            if (!input) return;

            navigator.clipboard.writeText(input.value).then(() => {
                const copyText = document.getElementById('copyText');
                const copyIcon = document.getElementById('copyIcon');

                if (copyText) copyText.textContent = 'Copied!';
                if (copyIcon) {
                    copyIcon.innerHTML = `<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="me-1 text-dark"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>`;
                }

                setTimeout(() => {
                    if (copyText) copyText.textContent = 'Copy Token';
                    if (copyIcon) {
                        copyIcon.innerHTML = `<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="me-1"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" /></svg>`;
                    }
                }, 2500);
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        }
    </script>
</x-app-layout>
