<?php
session_start();
require __DIR__.'/config.php';
check_suspension($pdo);

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->query("SELECT content FROM page_content WHERE id = 1");
$raw  = $stmt->fetchColumn();
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = [];
}

$defaults = [
    'hero'     => ['title'=>'','subtitle'=>'','location'=>'','age'=>''],
    'skills'   => [],
    'projects' => [],
    'terminal' => [],
    'contact'  => ['email'=>'','discord'=>'','tz'=>'']
];
$data = array_replace_recursive($defaults, $data);

if (count($data['terminal'])===0) {
    $data['terminal'][] = ['prompt'=>'','output'=>['']];
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $data['hero'] = array_map('trim', $_POST['hero'] ?? []);

    $data['skills'] = array_values($_POST['skills'] ?? []);

    $projects = array_values($_POST['projects'] ?? []);
    foreach ($projects as &$pj) {
        $pj['tags'] = array_filter(array_map('trim', explode(',', $pj['tags'] ?? '')));
    }
    unset($pj);
    $data['projects'] = $projects;

    $terms = array_values($_POST['terminal'] ?? []);
    foreach ($terms as &$t) {
        $lines = explode("\n", $t['output'] ?? '');
        $t['output'] = array_filter(array_map('trim', $lines));
    }
    unset($t);
    $data['terminal'] = $terms;
    $data['contact'] = array_map('trim', $_POST['contact'] ?? []);

    $json = json_encode($data, JSON_PRETTY_PRINT);
    $upd = $pdo->prepare("UPDATE page_content SET content=? WHERE id=1");
    $upd->execute([$json]);

    header('Location: edit.php?success=1');
    exit;
}

function e($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Edit Panel</title>
  <style>
    body{font:14px/1.5 'JetBrains Mono',monospace;background:#111;color:#ddd;padding:2rem;}
    h1{color:#89CFF0;}
    fieldset{margin-bottom:1.5rem;border:1px solid #444;padding:1rem;position:relative;}
    legend{font-weight:bold;color:#fff;padding:0 0.5rem;}
    label{display:block;margin:0.5rem 0;}
    input,textarea{width:100%;padding:0.5rem;background:#222;border:1px solid #555;color:#ddd;}
    a.add-link, a.remove-block{color:#89CFF0;text-decoration:none;font-size:0.9rem;cursor:pointer;}
    a.remove-block{position:absolute;top:8px;right:10px;}
    button{background:#39FF14;border:none;color:#111;padding:0.6rem 1.2rem;cursor:pointer;font-weight:bold;}
    .success{color:#0f0;margin-bottom:1rem;}
  </style>
</head>
<body>
  <h1>Edit Page Settings</h1>
  <?php if (!empty($_GET['success'])): ?>
    <div class="success">✓ Changes saved!</div>
  <?php endif; ?>

  <form method="post">
    <!-- HERO -->
    <fieldset><legend>Hero Section</legend>
      <?php foreach(['title','subtitle','location','age'] as $k): ?>
        <label><?= ucfirst($k) ?>
          <input name="hero[<?= $k ?>]" value="<?= e($data['hero'][$k]) ?>">
        </label>
      <?php endforeach; ?>
    </fieldset>

    <!-- SKILLS -->
    <fieldset id="skills-sec">
      <legend>Skills</legend>
      <?php foreach($data['skills'] as $i=>$sk): ?>
        <fieldset class="skill-block">
          <legend>Skill #<?= $i+1 ?></legend>
          <a class="remove-block" onclick="removeBlock(this,'skill')">✕</a>
          <label>Title
            <input name="skills[<?= $i ?>][title]" value="<?= e($sk['title']) ?>">
          </label>
          <label>Description
            <textarea name="skills[<?= $i ?>][desc]"><?= e($sk['desc']) ?></textarea>
          </label>
        </fieldset>
      <?php endforeach; ?>
      <p><a onclick="addSkill();return false;" class="add-link">+ Add Skill</a></p>
    </fieldset>

    <!-- PROJECTS -->
    <fieldset id="projects-sec">
      <legend>Projects</legend>
      <?php foreach($data['projects'] as $i=>$pj): ?>
        <fieldset class="project-block">
          <legend>Project #<?= $i+1 ?></legend>
          <a class="remove-block" onclick="removeBlock(this,'project')">✕</a>
          <label>Title
            <input name="projects[<?= $i ?>][title]" value="<?= e($pj['title']) ?>">
          </label>
          <label>Subtitle
            <input name="projects[<?= $i ?>][subtitle]" value="<?= e($pj['subtitle']) ?>">
          </label>
          <label>Description
            <textarea name="projects[<?= $i ?>][desc]"><?= e($pj['desc']) ?></textarea>
          </label>
          <label>URL
            <input name="projects[<?= $i ?>][url]" value="<?= e($pj['url']) ?>">
          </label>
          <label>Tags (comma-separated)
            <input name="projects[<?= $i ?>][tags]" value="<?= e(implode(',',$pj['tags'])) ?>">
          </label>
        </fieldset>
      <?php endforeach; ?>
      <p><a onclick="addProject();return false;" class="add-link">+ Add Project</a></p>
    </fieldset>

    <!-- TERMINAL -->
    <fieldset id="terminal-sec">
      <legend>Terminal</legend>
      <?php foreach($data['terminal'] as $i=>$t): ?>
        <fieldset class="term-block">
          <legend>Command #<?= $i+1 ?></legend>
          <a class="remove-block" onclick="removeBlock(this,'term')">✕</a>
          <label>Prompt
            <input name="terminal[<?= $i ?>][prompt]" value="<?= e($t['prompt']) ?>">
          </label>
          <label>Outputs (one per line)
            <textarea name="terminal[<?= $i ?>][output]"><?= e(implode("\n",$t['output'])) ?></textarea>
          </label>
        </fieldset>
      <?php endforeach; ?>
      <p><a id="add-term" class="add-link">+ Add Command</a></p>
    </fieldset>

    <!-- CONTACT -->
    <fieldset><legend>Contact</legend>
      <?php foreach(['email','discord','tz'] as $k): ?>
        <label><?= ucfirst($k) ?>
          <input name="contact[<?= $k ?>]" value="<?= e($data['contact'][$k]) ?>">
        </label>
      <?php endforeach; ?>
    </fieldset>

    <button type="submit">Save Changes</button>
  </form>

  <script>
    function refreshSkills(){
      document.querySelectorAll('#skills-sec .skill-block').forEach((blk,i)=>{
        blk.querySelector('legend').textContent = `Skill #${i+1}`;
        blk.querySelector('input[name^="skills"]').setAttribute('name', `skills[${i}][title]`);
        blk.querySelector('textarea[name^="skills"]').setAttribute('name', `skills[${i}][desc]`);
      });
    }
    function refreshProjects(){
      document.querySelectorAll('#projects-sec .project-block').forEach((blk,i)=>{
        blk.querySelector('legend').textContent = `Project #${i+1}`;
        blk.querySelector('input[name^="projects"][name$="[title]"]').setAttribute('name', `projects[${i}][title]`);
        blk.querySelector('input[name^="projects"][name$="[subtitle]"]').setAttribute('name', `projects[${i}][subtitle]`);
        blk.querySelector('textarea[name^="projects"][name$="[desc]"]').setAttribute('name', `projects[${i}][desc]`);
        blk.querySelector('input[name^="projects"][name$="[url]"]').setAttribute('name', `projects[${i}][url]`);
        blk.querySelector('input[name^="projects"][name$="[tags]"]').setAttribute('name', `projects[${i}][tags]`);
      });
    }
    function refreshTerms(){
      document.querySelectorAll('#terminal-sec .term-block').forEach((blk,i)=>{
        blk.querySelector('legend').textContent = `Command #${i+1}`;
        blk.querySelector('input[name^="terminal"]').setAttribute('name', `terminal[${i}][prompt]`);
        blk.querySelector('textarea[name^="terminal"]').setAttribute('name', `terminal[${i}][output]`);
      });
    }

    function removeBlock(el,type){
      const sel = {
        skill: '.skill-block',
        project: '.project-block',
        term:   '.term-block'
      }[type];
      el.closest(sel).remove();
      refreshSkills();
      refreshProjects();
      refreshTerms();
    }

    function addSkill(){
      const sec = document.getElementById('skills-sec');
      const tpl = sec.querySelector('.skill-block').cloneNode(true);
      tpl.querySelectorAll('input,textarea').forEach(x=>x.value='');
      sec.insertBefore(tpl, sec.lastElementChild);
      refreshSkills();
    }
    function addProject(){
      const sec = document.getElementById('projects-sec');
      const tpl = sec.querySelector('.project-block').cloneNode(true);
      tpl.querySelectorAll('input,textarea').forEach(x=>x.value='');
      sec.insertBefore(tpl, sec.lastElementChild);
      refreshProjects();
    }
    document.getElementById('add-term').addEventListener('click', e=>{
      e.preventDefault();
      const sec = document.getElementById('terminal-sec');
      const tpl = sec.querySelector('.term-block').cloneNode(true);
      tpl.querySelectorAll('input,textarea').forEach(x=>x.value='');
      sec.insertBefore(tpl, sec.lastElementChild);
      refreshTerms();
    });

    document.addEventListener('DOMContentLoaded',()=>{
      refreshSkills();
      refreshProjects();
      refreshTerms();
    });
  </script>
</body>
</html>
