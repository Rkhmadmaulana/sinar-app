## Security Guidelines:
      - Implement role-based access control
      - Validate user permissions for each medical module
      - Log all access attempts
      - Use CSRF protection for all forms
      - Implement rate limiting for sensitive endpoints
      - Sanitize all inputs
      - Use HTTPS only for production
      
      ## Example Middleware:
      ```php
      class AuthenticateMedicalAccess
      {
          public function handle(Request $request, Closure $next, string $module)
          {
              if (!$request->user()->hasAccessTo($module)) {
                  Log::warning('Unauthorized access attempt', [
                      'user_id' => $request->user()->id,
                      'module' => $module,
                      'ip' => $request->ip()
                  ]);
                  abort(403);
              }
              
              return $next($request);
          }
      }
      ```