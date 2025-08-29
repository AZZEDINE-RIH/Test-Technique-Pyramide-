<!DOCTYPE html>
<html>
<head>
    <title>Task Assigned</title>
</head>
<body>
    <h1>You have been assigned a new task</h1>
    
    <p>Hello,</p>
    
    <p>You have been assigned to the following task:</p>
    
    <div style="padding: 15px; border: 1px solid #ccc; border-radius: 5px; margin: 15px 0;">
        <h2>{{ $task->title }}</h2>
        <p><strong>Description:</strong> {{ $task->description }}</p>
        <p><strong>Project:</strong> {{ $task->project->title }}</p>
        <p><strong>Status:</strong> {{ $task->is_completed ? 'Completed' : 'Pending' }}</p>
    </div>
    
    <p>Please login to your account to view more details and manage this task.</p>
    
    <p>Thank you,<br>
    The Project Management Team</p>
</body>
</html>