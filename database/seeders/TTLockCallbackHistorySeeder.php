<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TTLockCallbackHistory;

class TTLockCallbackHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample callback history data based on the log structure
        $sampleData = [
            [
                'lock_id' => '17974276',
                'lock_mac' => '6D:A8:CC:11:3E:A1',
                'admin' => 'nandz.id@gmail.com',
                'notify_type' => 1,
                'records' => [
                    [
                        'lockId' => 17974276,
                        'electricQuantity' => 49,
                        'serverDate' => 1761648673715,
                        'recordTypeFromLock' => 20,
                        'recordType' => 8,
                        'success' => 1,
                        'lockMac' => '6D:A8:CC:11:3E:A1',
                        'keyboardPwd' => '53405001121792',
                        'lockDate' => 1761648664000,
                        'username' => 'nandzo'
                    ]
                ],
                'record_type_from_lock' => 20,
                'record_type' => 8,
                'success' => 1,
                'username' => 'nandzo',
                'keyboard_pwd' => '53405001121792',
                'lock_date' => 1761648664000,
                'server_date' => 1761648673715,
                'electric_quantity' => 49,
                'event_type' => 'battery_low',
                'message' => 'Lock battery is low by user: nandzo (Battery: 49%)',
                'raw_data' => [
                    'lockId' => '17974276',
                    'notifyType' => '1',
                    'records' => '[{"lockId":17974276,"electricQuantity":49,"serverDate":1761648673715,"recordTypeFromLock":20,"recordType":8,"success":1,"lockMac":"6D:A8:CC:11:3E:A1","keyboardPwd":"53405001121792","lockDate":1761648664000,"username":"nandzo"}]',
                    'admin' => 'nandz.id@gmail.com',
                    'lockMac' => '6D:A8:CC:11:3E:A1',
                    'request_id' => '9b7b2811-4ed9-4cc7-89be-70ee4ab45e8d'
                ],
                'request_id' => '9b7b2811-4ed9-4cc7-89be-70ee4ab45e8d',
                'processed' => true,
                'processed_at' => now(),
            ],
            [
                'lock_id' => '17974276',
                'lock_mac' => '6D:A8:CC:11:3E:A1',
                'admin' => 'nandz.id@gmail.com',
                'notify_type' => 1,
                'records' => [
                    [
                        'lockId' => 17974276,
                        'electricQuantity' => 85,
                        'serverDate' => 1761648700000,
                        'recordTypeFromLock' => 1,
                        'recordType' => 1,
                        'success' => 1,
                        'lockMac' => '6D:A8:CC:11:3E:A1',
                        'keyboardPwd' => null,
                        'lockDate' => 1761648695000,
                        'username' => 'nandzo'
                    ]
                ],
                'record_type_from_lock' => 1,
                'record_type' => 1,
                'success' => 1,
                'username' => 'nandzo',
                'keyboard_pwd' => null,
                'lock_date' => 1761648695000,
                'server_date' => 1761648700000,
                'electric_quantity' => 85,
                'event_type' => 'lock_operation',
                'message' => 'Lock operation received by user: nandzo (Battery: 85%)',
                'raw_data' => [
                    'lockId' => '17974276',
                    'notifyType' => '1',
                    'records' => '[{"lockId":17974276,"electricQuantity":85,"serverDate":1761648700000,"recordTypeFromLock":1,"recordType":1,"success":1,"lockMac":"6D:A8:CC:11:3E:A1","keyboardPwd":null,"lockDate":1761648695000,"username":"nandzo"}]',
                    'admin' => 'nandz.id@gmail.com',
                    'lockMac' => '6D:A8:CC:11:3E:A1',
                    'request_id' => '9b7b2811-4ed9-4cc7-89be-70ee4ab45e8d'
                ],
                'request_id' => '9b7b2811-4ed9-4cc7-89be-70ee4ab45e8d',
                'processed' => true,
                'processed_at' => now()->subMinutes(30),
            ],
        ];

        foreach ($sampleData as $data) {
            TTLockCallbackHistory::create($data);
        }
    }
}
