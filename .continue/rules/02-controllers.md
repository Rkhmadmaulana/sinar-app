## Controller Guidelines:
      - Keep controllers thin, move business logic to services
      - Use Form Request classes for validation
      - Return proper HTTP status codes
      - Use resource controllers for CRUD operations
      - Implement proper authorization using policies or gates
      - Use dependency injection for services
      - Handle exceptions gracefully
      - For medical data, always log access and modifications
      
      ## Example Structure:
      ```php
      class PasienController extends Controller
      {
          public function __construct(
              private PasienService $pasienService,
              private AuditService $auditService
          ) {}
          
          public function index(PasienIndexRequest $request)
          {
              $this->authorize('viewAny', Pasien::class);
              return $this->pasienService->getPaginatedList($request->validated());
          }
      }
      ```