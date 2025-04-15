<?php
session_start();
include 'database.php';

if (!isset($_SESSION["username"])) {
    header("Location: masuk.php");
    exit();
}

// Tambah Task
if (isset($_POST['add_task'])) {
    $tasklabel = $_POST['task'];
    $deadline = $_POST['deadline'];
    $priority = $_POST['priority'];
    $q_insert = "INSERT INTO tasks (tasklabel, taskstatus, deadline, priority) VALUES ('$tasklabel', 'open', '$deadline', '$priority')";
    mysqli_query($conn, $q_insert);
    header('Location: index.php');
}

// Update Task
if (isset($_POST['update_task'])) {
    $taskid = $_POST['taskid'];
    $tasklabel = $_POST['task'];
    $deadline = $_POST['deadline'];
    $priority = $_POST['priority'];
    $q_update = "UPDATE tasks SET tasklabel='$tasklabel', deadline='$deadline', priority='$priority' WHERE taskid='$taskid'";
    mysqli_query($conn, $q_update);
    header("Location: index.php");
}

// Tambah Subtask
if (isset($_POST['add_subtask'])) {
    $taskid = $_POST['taskid'];
    $subtasklabel = $_POST['subtask'];
    if (!empty($subtasklabel)) {
        $q_sub_insert = "INSERT INTO subtasks (taskid, subtasklabel, subtaskstatus) VALUES ('$taskid', '$subtasklabel', 'open')";
        mysqli_query($conn, $q_sub_insert);
    }
    header('Location: index.php');
}

// Update status Task
if (isset($_GET['done'])) {
    $status = ($_GET['status'] == 'open') ? 'close' : 'open';
    mysqli_query($conn, "UPDATE tasks SET taskstatus = '$status' WHERE taskid = '" . $_GET['done'] . "'");
    header('Location: index.php');
}

// Update status Subtask
if (isset($_GET['subdone'])) {
    $status = ($_GET['status'] == 'open') ? 'close' : 'open';
    mysqli_query($conn, "UPDATE subtasks SET subtaskstatus = '$status' WHERE subtaskid = '" . $_GET['subdone'] . "'");
    header('Location: index.php');
}

// Hapus Task
if (isset($_GET['delete'])) {
    $taskid = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM tasks WHERE taskid = '$taskid'");
    mysqli_query($conn, "DELETE FROM subtasks WHERE taskid = '$taskid'");
    header('Location: index.php');
}

$q_select = "SELECT * FROM tasks WHERE taskstatus = 'open' ORDER BY taskid DESC";
$run_q_select = mysqli_query($conn, $q_select);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>To Do List</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        * { padding: 0; margin: 0; box-sizing: border-box; }
        body {
            font-family: 'Roboto', sans-serif;
            background: url('https://source.unsplash.com/1600x900/?nature,water,landscape,sky,clouds') no-repeat center center fixed;
            background-size: cover;
            color: blue;
        }
        .container {
            width: 600px;
            margin: 50px auto;
            background: rgba(50, 111, 182, 0.8);
            padding: 20px;
            border-radius: 10px;
            color: white;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        }
        .header { text-align: center; margin-bottom: 20px; }
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgb(161, 10, 93);
            color: white;
            padding: 10px;
            border-radius: 8px;
            text-decoration: none;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }
        .card { padding: 15px; border-radius: 5px; margin-bottom: 10px; background: rgba(255, 255, 255, 0.9); color: black; }
        .high { background: #ffcccb; }
        .low { background: #d4edda; }
        .input-control { width: 100%; padding: 10px; font-size: 16px; margin-bottom: 10px; border-radius: 5px; border: none; }
        button { padding: 10px 20px; cursor: pointer; background: #4e54c8; color: #fff; border: none; border-radius: 5px; }
        .task-item { display: flex; justify-content: space-between; padding: 10px; border-bottom: 1px solid #ddd; }
        .task-item.done span { text-decoration: line-through; color: #ccc; }
        .deadline { font-size: 14px; color: #333; }
        .deadline.overdue { color: red; font-weight: bold; }
        .subtask-container { margin-left: 20px; padding: 10px; background: rgba(0, 0, 0, 0.05); border-radius: 5px; }
        .subtask-item { display: flex; justify-content: space-between; align-items: center; padding: 5px; }
        .history-section { display: none; margin-top: 30px; padding: 15px; background: rgba(255, 255, 255, 0.9); border-radius: 10px; color: black; }
    </style>
    <script>
        function toggleHistory() {
            var historySection = document.getElementById("history-section");
            historySection.style.display = (historySection.style.display === "block") ? "none" : "block";
        }
    </script>
</head>
<body>

<a href="logout.php" class="logout-btn">Logout</a>

<div class="container">
    <div class="header">
        <h2>To Do List</h2>
        <p><?= date("l, d M Y") ?></p>
    </div>

    <?php if (isset($_GET['edit'])):
        $edit_id = $_GET['edit'];
        $edit_q = mysqli_query($conn, "SELECT * FROM tasks WHERE taskid = '$edit_id'");
        $edit_task = mysqli_fetch_array($edit_q);
    ?>
    <div class="card">
        <form action="" method="post">
            <input type="hidden" name="taskid" value="<?= $edit_task['taskid'] ?>">
            <input type="text" name="task" class="input-control" value="<?= $edit_task['tasklabel'] ?>" required>
            <input type="date" name="deadline" class="input-control" value="<?= $edit_task['deadline'] ?>" required>
            <select name="priority" class="input-control" required>
                <option value="low" <?= $edit_task['priority'] == 'low' ? 'selected' : '' ?>>Kurang Penting</option>
                <option value="high" <?= $edit_task['priority'] == 'high' ? 'selected' : '' ?>>Penting</option>
            </select>
            <button type="submit" name="update_task">Simpan Perubahan</button>
        </form>
    </div>
    <?php else: ?>
    <div class="card">
        <form action="" method="post">
            <input type="text" name="task" class="input-control" placeholder="Tambahkan tugas" required>
            <input type="date" name="deadline" class="input-control" required>
            <select name="priority" class="input-control" required>
                <option value="low">Kurang Penting</option>
                <option value="high">Penting</option>
            </select>
            <button type="submit" name="add_task">Tambah Task</button>
        </form>
    </div>
    <?php endif; ?>

    <?php while ($task = mysqli_fetch_array($run_q_select)) { ?>
        <div class="card <?= $task['priority'] == 'high' ? 'high' : 'low' ?>">
            <div class="task-item <?= $task['taskstatus'] == 'close' ? 'done' : '' ?>">
                <span><?= $task['tasklabel'] ?></span>
                <div>
                    <a href="?done=<?= $task['taskid'] ?>&status=<?= $task['taskstatus'] ?>">
                        <?= $task['taskstatus'] == 'open' ? 'Selesai' : 'Buka lagi' ?>
                    </a>
                    |
                    <a href="?edit=<?= $task['taskid'] ?>">Edit</a>
                    |
                    <a href="?delete=<?= $task['taskid'] ?>" onclick="return confirm('Hapus task ini?')">Hapus</a>
                </div>
            </div>
            <p class="deadline <?= (strtotime($task['deadline']) < time() && $task['taskstatus'] == 'open') ? 'overdue' : '' ?>">
                Deadline: <?= date("d M Y", strtotime($task['deadline'])) ?>
            </p>

            <!-- Subtasks -->
            <div class="subtask-container">
                <?php
                $taskid = $task['taskid'];
                $sub_q = mysqli_query($conn, "SELECT * FROM subtasks WHERE taskid = '$taskid'");
                while ($sub = mysqli_fetch_array($sub_q)) {
                ?>
                <div class="subtask-item <?= $sub['subtaskstatus'] == 'close' ? 'done' : '' ?>">
                    <span><?= $sub['subtasklabel'] ?></span>
                    <a href="?subdone=<?= $sub['subtaskid'] ?>&status=<?= $sub['subtaskstatus'] ?>">
                        <?= $sub['subtaskstatus'] == 'open' ? 'Selesai' : 'Buka lagi' ?>
                    </a>
                </div>
                <?php } ?>

                <form action="" method="post">
                    <input type="hidden" name="taskid" value="<?= $taskid ?>">
                    <input type="text" name="subtask" class="input-control" placeholder="Tambah subtask..." required>
                    <button type="submit" name="add_subtask">Tambah Subtask</button>
                </form>
            </div>
        </div>
    <?php } ?>

    <!-- Tombol dan Riwayat -->
    <div style="text-align:center; margin-top: 20px;">
        <button onclick="toggleHistory()">Tampilkan Riwayat</button>
    </div>

    <div class="history-section" id="history-section">
        <h3>Riwayat Tugas Selesai</h3>
        <?php
        $history_q = mysqli_query($conn, "SELECT * FROM tasks WHERE taskstatus = 'close' ORDER BY taskid DESC");
        if (mysqli_num_rows($history_q) == 0) {
            echo "<p>Tidak ada tugas yang telah diselesaikan.</p>";
        }
        while ($history = mysqli_fetch_array($history_q)) {
        ?>
            <div class="card <?= $history['priority'] == 'high' ? 'high' : 'low' ?>">
                <div class="task-item done">
                    <span><?= $history['tasklabel'] ?></span>
                    <span>(Selesai)</span>
                </div>
                <p class="deadline">Deadline: <?= date("d M Y", strtotime($history['deadline'])) ?></p>
                <div class="subtask-container">
                    <?php
                    $tid = $history['taskid'];
                    $sub_history_q = mysqli_query($conn, "SELECT * FROM subtasks WHERE taskid = '$tid' AND subtaskstatus = 'close'");
                    if (mysqli_num_rows($sub_history_q) == 0) {
                        echo "<p style='font-size:14px;'>Tidak ada subtask yang diselesaikan.</p>";
                    }
                    while ($sub_history = mysqli_fetch_array($sub_history_q)) {
                    ?>
                        <div class="subtask-item done">
                            <span><?= $sub_history['subtasklabel'] ?></span>
                            <span>(Selesai)</span>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
</body>
</html>
