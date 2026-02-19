<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .wrapper { width: 100%; max-width: 600px; margin: 20px auto; border: 1px solid #ddd; border-radius: 12px; overflow: hidden; }
        .header { background-color: #842D62; color: #ffffff; padding: 20px; text-align: center; }
        .content { padding: 30px; background-color: #ffffff; }
        .footer { background-color: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #777; }
        .message-box { background: #f1f1f1; border-left: 4px solid #842D62; padding: 15px; margin: 20px 0; font-style: italic; }
        .user-name { font-weight: bold; color: #842D62; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h2 style="margin:0;">Retali Notification</h2>
        </div>
        <div class="content">
            <p>Halo, <span class="user-name">{{ $username }}</span></p>
            <p>Anda menerima notifikasi baru dari sistem:</p>
            <div class="message-box">
                "{{ $pesan }}"
            </div>
            <p>Silakan login ke aplikasi untuk melihat detail lengkapnya.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Retali Project. Sent to: {{ auth()->user()->email }}
        </div>
    </div>
</body>
</html>
