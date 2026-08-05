<?php
// Define the file where tasks will be saved
$file = 'tasks.txt';

// Check if the file exists; if not, create it
if (!file_exists($file)) {
    file_put_contents($file, "");
}

// Handle form submissions and actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. ADD TASK
    if (isset($_POST['add_task'])) {
        $task = trim(htmlspecialchars($_POST['task_text']));
        
        // Edge case: Handle empty input
        if (!empty($task)) {
            // Format: Task Text | Completed (0 = No, 1 = Yes)
            $line = $task . "|0\n";
            file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
        }
    }
    
    // 2. COMPLETE TASK
    if (isset($_POST['complete_task'])) {
        $line_to_complete = (int)$_POST['task_id'];
        $tasks = file($file, FILE_IGNORE_NEW_LINES);
        
        if (isset($tasks[$line_to_complete])) {
            // Split data, flip the completed bit to 1
            list($task_text, $status) = explode('|', $tasks[$line_to_complete]);
            $tasks[$line_to_complete] = $task_text . "|1";
            
            // Save the updated array back to the file
            file_put_contents($file, implode("\n", $tasks) . "\n", LOCK_EX);
        }
    }
    
    // 3. DELETE TASK
    if (isset($_POST['delete_task'])) {
        $line_to_delete = (int)$_POST['task_id'];
        $tasks = file($file, FILE_IGNORE_NEW_LINES);
        
        if (isset($tasks[$line_to_delete])) {
            // Remove the task from the array
            unset($tasks[$line_to_delete]);
            
            // Rewrite the remaining tasks, handling the edge case of an empty array
            if (empty($tasks)) {
                file_put_contents($file, "");
            } else {
                file_put_contents($file, implode("\n", $tasks) . "\n", LOCK_EX);
            }
        }
    }
    
    // Redirect to the same page to prevent form re-submission on refresh
    header("Location: index.php");
    exit();
}

// Read current tasks for rendering
$tasks = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP To-Do List</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; margin: 50px auto; max-width: 500px; }
        .container { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        .input-group { display: flex; margin-bottom: 20px; }
        .input-group input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px 0 0 4px; }
        .input-group button { padding: 10px 20px; border: none; background: #28a745; color: white; border-radius: 0 4px 4px 0; cursor: pointer; }
        ul { list-style: none; padding: 0; }
        li { display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #eee; }
        li.completed .task-text { text-decoration: line-through; color: #888; }
        .actions form { display: inline; }
        .btn-complete { background: #007bff; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; }
        .btn-delete { background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; }
    </style>
</head>
<body>

<div class="container">
    <h2>My To-Do List</h2>

    <form action="index.php" method="POST" class="input-group">
        <input type="text" name="task_text" placeholder="Add a new task..." required>
        <button type="submit" name="add_task">Add</button>
    </form>

    <ul>
        <?php if (!empty($tasks)): ?>
            <?php 
            // Using a loop to iterate through task array data
            $id = 0; 
            while ($id < count($tasks)): 
                list($task_text, $status) = explode('|', $tasks[$id]);
                $is_completed = ($status == '1');
            ?>
                <li class="<?php echo $is_completed ? 'completed' : ''; ?>">
                    <span class="task-text"><?php echo $task_text; ?></span>
                    
                    <div class="actions">
                        <?php if (!$is_completed): ?>
                            <form action="index.php" method="POST">
                                <input type="hidden" name="task_id" value="<?php echo $id; ?>">
                                <button type="submit" name="complete_task" class="btn-complete">✓</button>
                            </form>
                        <?php endif; ?>

                        <form action="index.php" method="POST">
                            <input type="hidden" name="task_id" value="<?php echo $id; ?>">
                            <button type="submit" name="delete_task" class="btn-delete">✗</button>
                        </form>
                    </div>
                </li>
            <?php 
                $id++;
            endwhile; 
            ?>
        <?php else: ?>
            <p style="text-align: center; color: #666;">No tasks yet! Add one above.</p>
        <?php endif; ?>
    </ul>
</div>

</body>
</html>