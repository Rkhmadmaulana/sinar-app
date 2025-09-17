## Blade Template Guidelines:
      - Use components for reusable UI elements
      - Escape all user data with {{ }} syntax
      - Implement proper CSRF tokens in forms
      - Use Laravel's validation error display
      - Create consistent layouts for different modules
      - Implement breadcrumbs for navigation
      - Use responsive design for mobile access
      
      ## Template Structure:
      ```blade
      @extends('layout.app')
      
      @section('title', 'Data Pasien')
      
      @section('content')
      <div class="container">
          <x-breadcrumb :items="$breadcrumbs" />
          
          <div class="card">
              <div class="card-header">
                  <h4>{{ $title }}</h4>
              </div>
              <div class="card-body">
                  @include('components.data-table')
              </div>
          </div>
      </div>
      @endsection
      ```
