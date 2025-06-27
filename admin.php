<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

session_start();
require __DIR__.'/config.php';
check_suspension($pdo);

if (empty($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$tab = $_GET['tab'] ?? 'home';

if ($tab === 'users') {
  $users = $pdo
    ->query("SELECT id, username, created_at, suspended FROM users ORDER BY created_at DESC")
    ->fetchAll(PDO::FETCH_ASSOC);
}

function e($v){ return htmlspecialchars($v,ENT_QUOTES,'UTF-8'); }
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel – <?= ucfirst($tab) ?></title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="adminpanel.css">
</head>
<body>
  <header>
    <h1>Admin<span> Panel</span></h1>
    <nav>
      <a href="admin.php?tab=home"  class="<?= $tab==='home'  ?'active':'' ?>">Home</a>
      <a href="admin.php?tab=users" class="<?= $tab==='users'?'active':'' ?>">Users</a>
      <a href="edit.php">Edit Site</a>
      <a href="logout.php">Log Out</a>
    </nav>
  </header>

  <main>
    <?php if ($tab==='home'): ?>
      <section id="home">
        <h2>Welcome, <?= e($_SESSION['username']) ?>!</h2>
        <p>Use the navigation above to manage users or edit the site content.</p>
      </section>

    <?php elseif ($tab==='users'): ?>
      <section id="users">
        <h2>Registered Users</h2>
        <?php if (empty($users)): ?>
          <p>No users found.</p>
        <?php else: ?>
          <table class="user-table">
            <thead>
              <tr>
                <th>Username</th>
                <th>Joined</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="users-tbody">
              <?php foreach($users as $u): ?>
              <tr data-id="<?= $u['id'] ?>">
                <td><?= e($u['username']) ?></td>
                <td><?= e($u['created_at']) ?></td>
                <td class="status-cell">
                  <?php if ($u['suspended']): ?>
                    <span class="badge suspended">Suspended</span>
                  <?php else: ?>
                    <span class="badge active">Active</span>
                  <?php endif; ?>
                </td>
                <td class="actions">
                  <a href="edit_user.php?id=<?= $u['id'] ?>"
                     class="btn-action edit" title="Edit">
                    <img src="icons/edit.svg" aria-hidden="true"><span>Edit</span>
                  </a>
                  <a href="#"
                     class="btn-action toggle <?= $u['suspended']?'resume':'suspend' ?>"
                     data-id="<?= $u['id'] ?>"
                     title="<?= $u['suspended']?'Unsuspend':'Suspend' ?>">
                    <img src="icons/<?= $u['suspended']?'play':'pause' ?>.svg" aria-hidden="true">
                    <span><?= $u['suspended']?'Unsuspend':'Suspend' ?></span>
                  </a>
                  <a href="#"
                     class="btn-action delete"
                     data-id="<?= $u['id'] ?>"
                     title="Delete">
                    <img src="icons/trash.svg" aria-hidden="true"><span>Delete</span>
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </section>
    <?php else: ?>
      <section><p>Unknown tab: <?= e($tab) ?></p></section>
    <?php endif; ?>
  </main>

  <?php if ($tab==='users'): ?>
  <script>
  document.querySelectorAll('a.toggle').forEach(link=>{
    link.addEventListener('click', async e=>{
      e.preventDefault();
      const btn = link, id = btn.dataset.id;
      btn.classList.add('disabled');
      try {
        const form = new URLSearchParams([['id',id]]);
        const res = await fetch('toggle_user.php', {
          method:'POST',
          headers:{'Content-Type':'application/x-www-form-urlencoded'},
          body: form
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);

        const tr = btn.closest('tr'),
              bd = tr.querySelector('.status-cell .badge');
        if (data.suspended) {
          bd.textContent='Suspended';
          bd.classList.replace('active','suspended');
        } else {
          bd.textContent='Active';
          bd.classList.replace('suspended','active');
        }

        btn.title = data.suspended ? 'Unsuspend':'Suspend';
        btn.classList.toggle('suspend', !data.suspended);
        btn.classList.toggle('resume',  data.suspended);
        btn.querySelector('img').src =
          `icons/${data.suspended?'play':'pause'}.svg`;
        btn.querySelector('span').textContent =
          data.suspended?'Unsuspend':'Suspend';

      } catch(err) {
        alert('Error: '+err.message);
      } finally {
        btn.classList.remove('disabled');
      }
    });
  });

  document.querySelectorAll('a.delete').forEach(link=>{
    link.addEventListener('click', async e=>{
      e.preventDefault();
      if (!confirm('Really delete this user?')) return;
      const id = link.dataset.id;
      try {
        const form = new URLSearchParams([['id',id]]);
        const res = await fetch('delete_user.php', {
          method:'POST',
          headers:{'Content-Type':'application/x-www-form-urlencoded'},
          body: form
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);
        link.closest('tr').remove();
      } catch(err) {
        alert('Delete failed: '+err.message);
      }
    });
  });
  </script>
  <?php endif; ?>
</body>
</html>
