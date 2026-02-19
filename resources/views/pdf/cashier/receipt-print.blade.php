<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Receipt - {{ $hblReference }}</title>
</head>
<body>
    <script>
        // Immediately redirect to PDF and trigger print
        window.location.href = '{{ $pdfUrl }}#toolbar=0&navpanes=0&scrollbar=0';
        
        // Trigger print after a short delay
        setTimeout(function() {
            window.print();
        }, 1000);
    </script>
</body>
</html>
