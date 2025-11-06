# Queue Setup Guide

## Cara Menjalankan Queue Worker

Queue worker diperlukan untuk memproses PDF conversion secara asynchronous.

### Development (Windows/Mac/Linux)

#### Opsi 1: Queue Worker (Simple)
Jalankan di terminal terpisah:
```bash
php artisan queue:work
```

Untuk auto-restart saat code berubah:
```bash
php artisan queue:work --tries=3 --timeout=300
```

#### Opsi 2: Queue Listen (Auto-reload)
```bash
php artisan queue:listen
```

#### Opsi 3: Process Once (Testing)
```bash
php artisan queue:work --once
```

### Production

#### Menggunakan Supervisor (Linux)

1. Install Supervisor:
```bash
sudo apt-get install supervisor
```

2. Buat config file `/etc/supervisor/conf.d/kfa-queue-worker.conf`:
```ini
[program:kfa-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/kfa-highlight/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/kfa-highlight/storage/logs/worker.log
stopwaitsecs=3600
```

3. Reload Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start kfa-queue-worker:*
```

#### Menggunakan Laravel Horizon (Redis)
Jika menggunakan Redis queue:
```bash
php artisan horizon
```

### Windows (Development)

#### Method 1: PowerShell
```powershell
php artisan queue:work
```

#### Method 2: Windows Task Scheduler
1. Buka Task Scheduler
2. Create Basic Task
3. Set trigger (At startup)
4. Set action: `php.exe` dengan argument `artisan queue:work`
5. Set working directory ke project root

## Monitoring Queue

### Check Queue Status
```bash
php artisan queue:monitor database
```

### Check Failed Jobs
```bash
php artisan queue:failed
```

### Retry Failed Jobs
```bash
# Retry specific job
php artisan queue:retry {job-id}

# Retry all failed jobs
php artisan queue:retry all
```

### Clear Failed Jobs
```bash
php artisan queue:flush
```

### Check Jobs Table
```bash
php artisan tinker
>>> DB::table('jobs')->count()
>>> DB::table('jobs')->get()
```

## Testing Conversion

### 1. Upload PDF via web interface
- Login ke dashboard
- Upload → Pilih PDF file
- Submit
- Status akan "Conversion in progress..."

### 2. Check Queue
```bash
php artisan tinker
>>> \App\Models\PdfUpload::latest()->first()->conversion_status
```

### 3. Process Queue Manually
```bash
php artisan queue:work --once
```

### 4. Check Result
Reload halaman welcome, image akan tampil jika konversi berhasil.

## Troubleshooting

### Queue tidak jalan
```bash
# Check if jobs exist
php artisan tinker
>>> DB::table('jobs')->count()

# Process manually
php artisan queue:work --once --verbose
```

### Job Failed
```bash
# Check failed jobs
php artisan queue:failed

# Check logs
tail -f storage/logs/laravel.log
```

### Memory Issues
Increase PHP memory limit di `php.ini`:
```ini
memory_limit = 512M
```

Atau set di command:
```bash
php -d memory_limit=512M artisan queue:work
```

### Timeout Issues
Increase timeout:
```bash
php artisan queue:work --timeout=600
```

## Development Tips

### Auto-restart on Code Changes
Install `chokidar`:
```bash
npm install -g chokidar-cli
```

Create npm script di `package.json`:
```json
"scripts": {
  "queue:watch": "chokidar '**/*.php' -c 'php artisan queue:restart'"
}
```

Run:
```bash
npm run queue:watch
```

### Queue Worker Helper Script
Buat file `start-queue.sh` (Linux/Mac):
```bash
#!/bin/bash
while true; do
    php artisan queue:work --tries=3 --timeout=300
    sleep 1
done
```

Atau `start-queue.bat` (Windows):
```batch
@echo off
:start
php artisan queue:work --tries=3 --timeout=300
timeout /t 1 /nobreak
goto start
```

## Environment Variables

Pastikan `.env` sudah configured:
```env
QUEUE_CONNECTION=database
```

Alternatif queue drivers:
- `sync` - Process immediately (no queue, for development)
- `database` - Store jobs in database (default)
- `redis` - Use Redis (faster, recommended for production)
- `beanstalkd` - Use Beanstalkd
- `sqs` - AWS SQS

## Performance Tuning

### Multiple Workers
```bash
# Start 3 workers
php artisan queue:work --sleep=3 --tries=3 &
php artisan queue:work --sleep=3 --tries=3 &
php artisan queue:work --sleep=3 --tries=3 &
```

### Worker Options
```bash
php artisan queue:work \
    --queue=default,high,low \
    --sleep=3 \
    --tries=3 \
    --max-jobs=1000 \
    --max-time=3600 \
    --timeout=300
```

- `--sleep` - Seconds to wait when no jobs available
- `--tries` - Number of retry attempts
- `--max-jobs` - Stop after processing N jobs
- `--max-time` - Stop after N seconds
- `--timeout` - Job timeout in seconds

## Recommended Setup

### Development
```bash
# Terminal 1: Dev server
npm run dev

# Terminal 2: Queue worker
php artisan queue:listen
```

### Production
- Use Supervisor with 2-4 workers
- Or use Laravel Horizon with Redis
- Monitor with `queue:monitor` or Horizon dashboard
- Set up alerts for failed jobs
