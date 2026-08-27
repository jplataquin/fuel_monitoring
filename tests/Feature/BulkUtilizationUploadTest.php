<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\ChargeableAccount;
use App\Models\SubAccount;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BulkUtilizationUploadTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Asset $asset;
    private AssetType $assetType;
    private ChargeableAccount $account;
    private SubAccount $subAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $this->admin = User::factory()->create(['role' => 'administrator']);
        $this->assetType = AssetType::create(['name' => 'Truck']);
        $this->asset = Asset::create([
            'fleet_no' => 'T-500',
            'asset_type_id' => $this->assetType->id,
            'fuel_factor_km' => 3.0,
            'fuel_factor_hr' => 2.0,
            'tank_capacity' => 150,
            'last_kilometer_reading' => 1000.0,
            'last_engine_hours' => 50.0,
            'last_date' => '2026-08-25',
            'last_time' => '12:00',
        ]);

        $this->account = ChargeableAccount::create([
            'name' => 'Project Alpha',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $this->subAccount = SubAccount::create([
            'chargeable_account_id' => $this->account->id,
            'name' => 'Civil Works',
        ]);
    }

    private function createCsvFile(array $rows): UploadedFile
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_bulk_');
        $handle = fopen($tempFile, 'w');

        // Headers
        fputcsv($handle, [
            'Date',
            'Start Time',
            'End Time',
            'Personnel In-Charge',
            'Charged To',
            'Sub Account',
            'Calculation Type',
            'Start Reading',
            'End Reading',
            'Actual Hours',
            'Unbudgeted',
            'Particulars',
            'Reference',
            'Remarks'
        ]);

        // Rows
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);

        return new UploadedFile($tempFile, 'import.csv', 'text/csv', null, true);
    }

    private function createCombinedCsvFile(array $rows): UploadedFile
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_bulk_');
        $handle = fopen($tempFile, 'w');

        // Headers
        fputcsv($handle, [
            'Date',
            'Start Time',
            'End Time',
            'Personnel In-Charge',
            'Account :: Sub Account',
            'Calculation Type',
            'Start Reading',
            'End Reading',
            'Actual Hours',
            'Particulars',
            'Reference',
            'Remarks'
        ]);

        // Rows
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);

        return new UploadedFile($tempFile, 'import.csv', 'text/csv', null, true);
    }

    public function test_bulk_upload_view_renders_correctly(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('assets.utilization-entries.bulk-upload', $this->asset));

        $response->assertStatus(200);
        $response->assertSee('Bulk Upload Utilization: T-500');
        $response->assertSee('Drag and drop file here');
    }

    public function test_bulk_preview_rejects_empty_file(): void
    {
        $file = UploadedFile::fake()->create('empty.csv', 0);

        $response = $this->actingAs($this->admin)
            ->post(route('assets.utilization-entries.bulk-preview', $this->asset), [
                'file' => $file,
            ]);

        $response->assertStatus(422);
    }

    public function test_bulk_preview_rejects_more_than_50_rows(): void
    {
        $rows = [];
        for ($i = 0; $i < 51; $i++) {
            $rows[] = [
                '2026-08-26', '08:00', '10:00', 'Operator ' . $i, 'Project Alpha', 'Civil Works', 'Timeframe', '0', '0', '0', 'No', 'Task', 'REF-001', ''
            ];
        }

        $file = $this->createCsvFile($rows);

        $response = $this->actingAs($this->admin)
            ->post(route('assets.utilization-entries.bulk-preview', $this->asset), [
                'file' => $file,
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('error', 'Maximum allowable entries is 50 rows per bulk upload. The uploaded file has 51 rows.');
    }

    public function test_bulk_preview_validates_rows_and_highlights_errors(): void
    {
        $rows = [
            // Row 1: Valid Kilometer Reading
            ['2026-08-26', '08:00', '10:00', 'John Doe', 'Project Alpha', 'Civil Works', 'Kilometer Reading', '1000', '1050', '0', 'No', 'First run', 'REF-01', ''],
            // Row 2: Invalid Kilometer Reading (Odo Decrement)
            ['2026-08-26', '10:30', '12:00', 'John Doe', 'Project Alpha', 'Civil Works', 'Kilometer Reading', '1040', '1080', '0', 'No', 'Odo decrement', 'REF-02', ''],
            // Row 3: Invalid Account Name
            ['2026-08-26', '12:30', '14:00', 'John Doe', 'Non-existent Project', 'Civil Works', 'Timeframe', '0', '0', '0', 'No', 'Invalid project', 'REF-03', '']
        ];

        $file = $this->createCsvFile($rows);

        $response = $this->actingAs($this->admin)
            ->post(route('assets.utilization-entries.bulk-preview', $this->asset), [
                'file' => $file,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('has_errors', true);
        $response->assertJsonCount(3, 'rows');

        $previewRows = $response->json('rows');

        // Row 1 is valid
        $this->assertFalse($previewRows[0]['has_errors']);

        // Row 2 has Odo decrement error
        $this->assertTrue($previewRows[1]['has_errors']);
        $this->assertContains("Start Odometer (1040) cannot be less than previous log's End Odometer (1050).", $previewRows[1]['errors']);

        // Row 3 has Invalid Account error
        $this->assertTrue($previewRows[2]['has_errors']);
        $this->assertContains("Chargeable account 'Non-existent Project' not found.", $previewRows[2]['errors']);
    }

    public function test_bulk_preview_handles_combined_account_sub_account_format(): void
    {
        $rows = [
            // Row 1: Active account & sub-account combination
            ['2026-08-26', '08:00', '10:00', 'John Doe', 'Project Alpha :: Civil Works', 'Kilometer Reading', '1000', '1050', '0', 'First run', 'REF-01', ''],
            // Row 2: Account & Unbudgeted combination
            ['2026-08-26', '10:30', '12:00', 'John Doe', 'Project Alpha :: Unbudgeted', 'Kilometer Reading', '1050', '1100', '0', 'Unbudgeted run', 'REF-02', '']
        ];

        $file = $this->createCombinedCsvFile($rows);

        $response = $this->actingAs($this->admin)
            ->post(route('assets.utilization-entries.bulk-preview', $this->asset), [
                'file' => $file,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('has_errors', false);
        $response->assertJsonCount(2, 'rows');

        $previewRows = $response->json('rows');

        // Verify Row 1 parsed correct IDs and unbudgeted is false
        $this->assertEquals($this->account->id, $previewRows[0]['chargeable_account_id']);
        $this->assertEquals($this->subAccount->id, $previewRows[0]['sub_account_id']);
        $this->assertFalse($previewRows[0]['unbudgeted']);

        // Verify Row 2 parsed unbudgeted is true, sub_account_id is null
        $this->assertEquals($this->account->id, $previewRows[1]['chargeable_account_id']);
        $this->assertNull($previewRows[1]['sub_account_id']);
        $this->assertTrue($previewRows[1]['unbudgeted']);
    }

    public function test_bulk_store_saves_entries_inside_transaction_and_updates_asset_technical_specifications(): void
    {
        $validatedRows = [
            [
                'index' => 1,
                'date' => '2026-08-26',
                'start_time' => '08:00',
                'end_time' => '10:00',
                'driver_operator_name' => 'John Operator',
                'chargeable_account_id' => $this->account->id,
                'chargeable_account' => 'Project Alpha',
                'sub_account_id' => $this->subAccount->id,
                'sub_account' => 'Civil Works',
                'reference' => 'REF-01',
                'calculation_type' => 'Kilometer Reading',
                'unbudgeted' => false,
                'particulars' => 'Sequential task 1',
                'start_kilometer_reading' => 1000.0,
                'end_kilometer_reading' => 1050.0,
                'start_hour_reading' => 0.0,
                'end_hour_reading' => 0.0,
                'actual_hours' => 0.0,
                'remarks' => '',
            ],
            [
                'index' => 2,
                'date' => '2026-08-26',
                'start_time' => '11:00',
                'end_time' => '13:00',
                'driver_operator_name' => 'John Operator',
                'chargeable_account_id' => $this->account->id,
                'chargeable_account' => 'Project Alpha',
                'sub_account_id' => $this->subAccount->id,
                'sub_account' => 'Civil Works',
                'reference' => 'REF-02',
                'calculation_type' => 'Kilometer Reading',
                'unbudgeted' => false,
                'particulars' => 'Sequential task 2',
                'start_kilometer_reading' => 1050.0,
                'end_kilometer_reading' => 1110.0,
                'start_hour_reading' => 0.0,
                'end_hour_reading' => 0.0,
                'actual_hours' => 0.0,
                'remarks' => '',
            ]
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('assets.utilization-entries.bulk-store', $this->asset), [
                'rows' => $validatedRows
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Verify entries were created in DB
        $this->assertDatabaseHas('utilization_entries', [
            'asset_id' => $this->asset->id,
            'end_kilometer_reading' => 1050.0,
            'particulars' => 'Sequential task 1'
        ]);

        $this->assertDatabaseHas('utilization_entries', [
            'asset_id' => $this->asset->id,
            'end_kilometer_reading' => 1110.0,
            'particulars' => 'Sequential task 2'
        ]);

        // Verify asset's technical specifications were updated with final readings
        $this->asset->refresh();
        $this->assertEquals(1110.0, $this->asset->last_kilometer_reading);
        $this->assertEquals('2026-08-26', $this->asset->last_date);
        $this->assertEquals('13:00', $this->asset->last_time);
    }

    public function test_bulk_store_rolls_back_entire_transaction_if_single_row_fails_sequential_validation(): void
    {
        $validatedRows = [
            [
                'index' => 1,
                'date' => '2026-08-26',
                'start_time' => '08:00',
                'end_time' => '10:00',
                'driver_operator_name' => 'John Operator',
                'chargeable_account_id' => $this->account->id,
                'chargeable_account' => 'Project Alpha',
                'sub_account_id' => $this->subAccount->id,
                'sub_account' => 'Civil Works',
                'reference' => 'REF-01',
                'calculation_type' => 'Kilometer Reading',
                'unbudgeted' => false,
                'particulars' => 'Task 1',
                'start_kilometer_reading' => 1000.0,
                'end_kilometer_reading' => 1050.0,
                'start_hour_reading' => 0.0,
                'end_hour_reading' => 0.0,
                'actual_hours' => 0.0,
                'remarks' => '',
            ],
            [
                'index' => 2,
                'date' => '2026-08-26',
                'start_time' => '11:00',
                'end_time' => '13:00',
                'driver_operator_name' => 'John Operator',
                'chargeable_account_id' => $this->account->id,
                'chargeable_account' => 'Project Alpha',
                'sub_account_id' => $this->subAccount->id,
                'sub_account' => 'Civil Works',
                'reference' => 'REF-02',
                'calculation_type' => 'Kilometer Reading',
                'unbudgeted' => false,
                // Sequential validation failure: start odo (1040) < end odo (1050)
                'particulars' => 'Failed Task 2',
                'start_kilometer_reading' => 1040.0,
                'end_kilometer_reading' => 1100.0,
                'start_hour_reading' => 0.0,
                'end_hour_reading' => 0.0,
                'actual_hours' => 0.0,
                'remarks' => '',
            ]
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('assets.utilization-entries.bulk-store', $this->asset), [
                'rows' => $validatedRows
            ]);

        $response->assertStatus(422);

        // Verify that NO entries were added in database (transaction rollback)
        $this->assertDatabaseMissing('utilization_entries', [
            'asset_id' => $this->asset->id,
            'particulars' => 'Task 1'
        ]);

        // Verify that asset was NOT updated
        $this->asset->refresh();
        $this->assertEquals(1000.0, $this->asset->last_kilometer_reading);
        $this->assertEquals('2026-08-25', $this->asset->last_date);
        $this->assertEquals('12:00', $this->asset->last_time);
    }

    public function test_bulk_template_downloads_successfully(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('assets.utilization-entries.bulk-template', $this->asset));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('Content-Disposition', 'attachment; filename="' . $this->asset->fleet_no . ' - Bulk Utilization Upload.xlsx"');
    }

    public function test_bulk_upload_chunk_uploads_sequentially_and_parses(): void
    {
        $rows = [
            ['2026-08-26', '08:00', '10:00', 'John Doe', 'Project Alpha', 'Civil Works', 'Kilometer Reading', '1000', '1050', '0', 'No', 'First run', 'REF-01', '']
        ];
        $file = $this->createCsvFile($rows);

        // Upload in 2 chunks
        $fileSize = $file->getSize();
        $chunkSize = (int) ceil($fileSize / 2);

        // Chunk 1
        $tempFile1 = tempnam(sys_get_temp_dir(), 'chunk_');
        $in = fopen($file->getRealPath(), 'rb');
        $out = fopen($tempFile1, 'wb');
        stream_copy_to_stream($in, $out, $chunkSize);
        fclose($out);
        $chunk1 = new UploadedFile($tempFile1, 'import.csv', 'text/csv', null, true);

        $response1 = $this->actingAs($this->admin)
            ->post(route('assets.utilization-entries.bulk-upload-chunk', $this->asset), [
                'file_chunk' => $chunk1,
                'chunk_index' => 0,
                'total_chunks' => 2,
                'file_name' => 'import.csv',
                'file_id' => 'test_file_chunking_123',
            ]);

        $response1->assertStatus(200);
        $response1->assertJsonPath('success', true);
        $response1->assertJsonPath('progress', 50);

        // Chunk 2
        $tempFile2 = tempnam(sys_get_temp_dir(), 'chunk_');
        $out = fopen($tempFile2, 'wb');
        stream_copy_to_stream($in, $out);
        fclose($out);
        fclose($in);
        $chunk2 = new UploadedFile($tempFile2, 'import.csv', 'text/csv', null, true);

        $response2 = $this->actingAs($this->admin)
            ->post(route('assets.utilization-entries.bulk-upload-chunk', $this->asset), [
                'file_chunk' => $chunk2,
                'chunk_index' => 1,
                'total_chunks' => 2,
                'file_name' => 'import.csv',
                'file_id' => 'test_file_chunking_123',
            ]);

        $response2->assertStatus(200);
        $response2->assertJsonPath('success', true);
        $response2->assertJsonCount(1, 'rows');
        $this->assertFalse($response2->json('rows.0.has_errors'));
    }
}
