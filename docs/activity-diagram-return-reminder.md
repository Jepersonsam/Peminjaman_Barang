# Activity Diagram - Notifikasi Pengingat Pengembalian Barang

## Diagram Format Mermaid

```mermaid
graph TB
    subgraph Admin["Admin"]
        Start([Start]) --> AccessDashboard[Access Dashboard<br/>Manajemen Peminjaman]
        AccessDashboard --> ClickSendReminder[Click Tombol<br/>Kirim Notifikasi]
        ClickSendReminder --> ViewResult[View Result<br/>Success/Error Message]
        ViewResult --> End([End])
    end

    subgraph Frontend["Frontend"]
        ReceiveClick[Receive Click Event] --> SendPOSTRequest[Send POST Request<br/>/api/borrowings/{id}/send-return-reminder<br/>Headers: Authorization Token]
        SendPOSTRequest --> WaitResponse[Wait for Response]
        WaitResponse --> ReceiveResponse[Receive Response<br/>JSON Data]
        ReceiveResponse --> DisplayMessage[Display Success/Error<br/>Message to Admin]
    end

    subgraph Backend["Backend"]
        ReceiveRequest[Receive POST Request] --> ValidateAuth[Validate Authentication<br/>& Permission]
        ValidateAuth --> CheckBorrowing{Check Borrowing<br/>Exists?}
        CheckBorrowing -->|No| Return404[Return 404<br/>Not Found]
        CheckBorrowing -->|Yes| ValidateStatus{Is Returned?<br/>Is Approved?}
        ValidateStatus -->|No| Return400[Return 400<br/>Bad Request]
        ValidateStatus -->|Yes| CheckUser{User & Email<br/>Exists?}
        CheckUser -->|No| Return404User[Return 404<br/>User Not Found]
        CheckUser -->|Yes| CalculateDays[Calculate Days Remaining<br/>return_date - today]
        CalculateDays --> CreateNotification[Create ReturnReminderNotification<br/>with Borrowing & Days]
        CreateNotification --> SendEmail[Send Email via<br/>Mail Service]
        SendEmail --> LogResult[Log Result]
        LogResult --> ReturnSuccess[Return 200 Success<br/>with Data]
    end

    AccessDashboard --> ReceiveClick
    ClickSendReminder --> SendPOSTRequest
    SendPOSTRequest --> ReceiveRequest
    Return404 --> WaitResponse
    Return400 --> WaitResponse
    Return404User --> WaitResponse
    ReturnSuccess --> WaitResponse
    DisplayMessage --> ViewResult
```

## Penjelasan Alur

### 1. Admin Swimlane
- **Access Dashboard**: Admin mengakses halaman Manajemen Peminjaman
- **Click Send Reminder**: Admin mengklik tombol "Kirim Notifikasi" pada borrowing tertentu
- **View Result**: Admin melihat hasil (success atau error message)

### 2. Frontend Swimlane
- **Receive Click Event**: Frontend menerima event klik dari admin
- **Send POST Request**: Frontend mengirim POST request ke endpoint dengan token authorization
- **Wait for Response**: Frontend menunggu response dari backend
- **Receive Response**: Frontend menerima response JSON
- **Display Message**: Frontend menampilkan pesan success/error ke admin

### 3. Backend Swimlane
- **Receive POST Request**: Backend menerima request dari frontend
- **Validate Authentication**: Validasi token dan permission user
- **Check Borrowing**: Cek apakah borrowing dengan ID tersebut ada
- **Validate Status**: Validasi apakah borrowing sudah dikembalikan atau belum disetujui
- **Check User**: Cek apakah user dan email tersedia
- **Calculate Days Remaining**: Hitung hari tersisa (tanggal kembali - hari ini)
- **Create Notification**: Buat instance ReturnReminderNotification
- **Send Email**: Kirim email melalui mail service
- **Log Result**: Catat hasil pengiriman
- **Return Success**: Kembalikan response 200 dengan data

## Decision Points

1. **Check Borrowing Exists?**
   - No → Return 404
   - Yes → Continue

2. **Is Returned? Is Approved?**
   - No → Return 400 (Bad Request)
   - Yes → Continue

3. **User & Email Exists?**
   - No → Return 404
   - Yes → Continue to send email

## Error Handling

- **404 Not Found**: Borrowing atau User tidak ditemukan
- **400 Bad Request**: Borrowing sudah dikembalikan atau belum disetujui
- **500 Internal Server Error**: Gagal mengirim email (dicatat di log)

