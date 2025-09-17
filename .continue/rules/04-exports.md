 ## Medical Report Guidelines:
      - Always validate date ranges and parameters
      - Implement proper data sanitization for exports
      - Use Laravel Excel for complex exports
      - Create separate sheets for different data types
      - Include proper headers and metadata
      - Implement patient privacy controls
      - Add audit logging for report generation
      - Use queues for large report generation
      
      ## Export Structure:
      ```php
      class PasienMeninggalExport implements FromMultipleSheets
      {
          public function sheets(): array
          {
              return [
                  'Data' => new PasienMeninggalDataSheet($this->data),
                  'Summary' => new PasienMeninggalSummarySheet($this->summary),
              ];
          }
      }
      ```