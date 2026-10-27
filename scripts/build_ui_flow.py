from pathlib import Path
from html import escape
import re,posixpath,shutil,zipfile
root=Path(__file__).resolve().parents[1]
original=(root/'DNSC_Sign_In_Single_Card.html').read_text(encoding='utf-8')
css=re.search(r'<style>(.*?)</style>',original,re.S).group(1)
controls=(root/'assets/static.js').read_text(encoding='utf-8-sig')
archive=root/'backups'/'before-ui-flow-20261027'
modified=['Campus Sign In.html','DNSC_Sign_In_Single_Card.html','authentication/Authentication_Login.html','authentication/Authentication_Login_Single_Card.html','hr-admin/HR_Admin_Applicant_Management.html','DNSC_HR_Dashboard.html','hr-admin/HR_Admin_Dashboard.html','index.html']
for name in modified:
    p=root/name;dest=archive/(name+'.bak');dest.parent.mkdir(parents=True,exist_ok=True)
    if p.exists() and not dest.exists():shutil.copy2(p,dest)
def field(name,label,type='text',extra=''):return f'<label>{label}<input name="{name}" type="{type}" {extra}></label>'
def select(name,label,options,extra=''):return f'<label>{label}<select name="{name}" {extra}>'+''.join(f'<option value="{escape(v)}">{escape(t)}</option>' for v,t in options)+'</select></label>'
education=[('','Choose educational attainment'),('Bachelor’s Degree','Bachelor’s Degree'),('Master’s Degree','Master’s Degree'),('Doctorate','Doctorate'),('Other','Other')]
def prc():return field('prcNumber','PRC License Number','text','maxlength="40" required')+'<label class="checkline"><input type="checkbox" name="prcNotApplicable">Not Applicable — I do not hold a PRC license</label>'
def form(id,html,label):return '<form id="'+id+'" class="demo-form flow-form" data-flow-form novalidate><fieldset disabled>'+html+'<div><button type="submit" class="demo-button primary">'+label+'</button></div></fieldset></form>'
def panel(title,html):return '<section class="demo-panel"><h2>'+title+'</h2>'+html+'</section>'
def header(prefix=''):
    return '<a class="skip-link" href="#main-content">Skip to content</a><header class="hrms-header"><a class="hrms-brand" href="'+prefix+'index.html"><strong>DNSC SMART HRMS</strong><span>Human Resource Management System</span></a><nav class="hrms-public-nav" aria-label="Main navigation"><a href="'+prefix+'index.html">Home</a><a href="'+prefix+'job-vacancies.html">Job Vacancies</a><a href="'+prefix+'login.html">Sign In</a></nav><button id="theme-mode" class="icon-button" type="button" aria-label="Switch to dark mode"></button></header>'
message='<div class="flow-message" id="flow-message" role="status" tabindex="-1" hidden></div>'
def make(name,title,content,flow,auth=False,sidebar=''):
    prefix='../' if '/' in name else ''
    if auth:body=message+'<main class="shell" id="main-content">'+content+'</main>'
    else:
        body=header(prefix)
        if sidebar:body=body.replace('class="hrms-header"','class="hrms-header has-sidebar-menu"',1).replace('<header class="hrms-header has-sidebar-menu">','<header class="hrms-header has-sidebar-menu"><button type="button" class="hrms-menu icon-button" id="menu-toggle" aria-controls="portal-sidebar" aria-expanded="true" aria-label="Collapse navigation" title="Collapse navigation"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>',1)
        body+='<div class="'+('demo-layout hrms-layout flow-layout' if sidebar else 'public-layout')+'">'+sidebar+'<main class="demo-main" id="main-content">'+message+content+'</main></div>'
    html='<!doctype html>\n<html lang="en" data-theme="light" data-ready="true"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'+title+' | DNSC Smart HRMS</title><style>'+css+'</style><link rel="stylesheet" href="'+prefix+'assets/ui-flow.css"><script defer src="'+prefix+'assets/ui-model.js"></script><script defer src="'+prefix+'assets/ui-flow.js"></script></head><body class="flow-page'+(' original-login' if auth else '')+'" data-flow="'+flow+'">'+body+'<script>'+controls+'</script><noscript><p>Enable JavaScript to use this page.</p></noscript></body></html>'
    if sidebar:
        html=html.replace('class="flow-page"','class="flow-page sidebar-shell"',1).replace('</head>','<link rel="stylesheet" href="'+prefix+'assets/navigation.css"></head>')
    (root/name).write_text(html,encoding='utf-8')
brand='<div class="top"><a class="brand" href="index.html"><div class="logo-slot" aria-hidden="true">DNSC</div><div><div class="brand-name">DNSC SMART HRMS</div><span class="brand-sub">Human Resource Management System</span></div></a><button class="theme-btn" type="button" id="theme-mode" aria-label="Switch color theme"></button></div>'
google='<svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M22.5 12.27c0-.79-.07-1.54-.2-2.27H12v4.3h5.9a5.05 5.05 0 0 1-2.19 3.31v2.75h3.54c2.07-1.91 3.25-4.72 3.25-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.54-2.75c-.98.66-2.24 1.05-3.74 1.05-2.87 0-5.3-1.94-6.17-4.55H2.17v2.84A11 11 0 0 0 12 23z"/><path fill="#FBBC05" d="M5.83 14.09a6.6 6.6 0 0 1 0-4.18V7.07H2.17a11 11 0 0 0 0 9.86l3.66-2.84z"/><path fill="#EA4335" d="M12 5.36c1.62 0 3.07.56 4.21 1.65l3.15-3.15C17.46 2.09 14.97 1 12 1A11 11 0 0 0 2.17 7.07l3.66 2.84C6.7 7.3 9.13 5.36 12 5.36z"/></svg>'
login='<section class="left" aria-label="Account access">'+brand+'<div class="card"><div><h1>Welcome <span>back</span></h1><p class="lede">Sign in with credentials or continue with Google.</p></div>'+form('credential-login',field('identity','Email or Username','text','required autocomplete="off" maxlength="100"')+'<label>Password<div class="input-wrap"><input id="demo-password" name="password" type="password" required autocomplete="off" maxlength="72"><button type="button" class="reveal" data-reveal="demo-password" aria-pressed="false">Show</button></div></label>','Sign In')+'<div class="divider">or</div><button class="google-button" type="button" id="google-continue">'+google+'Continue with Google</button></div></section>'
make('login.html','Sign In',login,'login',True)
# Existing sign-in URLs share the same updated interface and behavior.
for alias in ['Campus Sign In.html','DNSC_Sign_In_Single_Card.html','authentication/Authentication_Login.html','authentication/Authentication_Login_Single_Card.html','authentication/Authentication_Applicant_Registration.html']:
    body=login.replace('href="index.html"','href="'+('../' if '/' in alias else '')+'index.html"')
    make(alias,'Sign In',body,'login',True)
verification='<header class="demo-heading"><h1>Instructor Verification</h1><p>Complete your applicant profile before choosing a vacancy.</p></header><section class="demo-panel verification-card"><h2>Applicant Information</h2>'+form('verification-form','<div class="form-grid">'+field('fullName','Full Name','text','required maxlength="150"')+field('email','Email Address','email','required maxlength="150"')+prc()+field('specialization','Profession / Specialization','text','required maxlength="150"')+select('education','Educational Attainment',education,'required')+'</div>','Submit and Continue')+'</section>'
make('applicant-verification.html','Instructor Verification',verification,'verification')
make('job-vacancies.html','Job Vacancies','<header class="demo-heading"><h1>Job Vacancies</h1><p>Explore teaching opportunities across DNSC departments.</p></header><div class="vacancy-grid" id="vacancy-grid"></div>','vacancies')
def upload(name,label,required=True,multiple=False):return '<label class="file-label">'+label+f'<input type="file" name="{name}" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg" '+('required ' if required else '')+('multiple ' if multiple else '')+'><span class="flow-hint" data-file-list="'+name+'">PDF, DOC, DOCX, PNG, JPG · up to 5 MB per file</span></label>'
application='<header class="demo-heading"><h1>Job Application Form</h1><p id="selected-job-heading">Choose a vacancy to begin.</p></header><section class="demo-panel" id="choose-vacancy" hidden><h2>Select a Position</h2><p>Choose an available position before opening this form.</p><a class="demo-button primary" href="job-vacancies.html">Browse Job Vacancies</a></section><div id="application-wrapper"><section class="demo-panel"><h2>Application Details</h2>'+form('application-form','<input name="jobId" type="hidden"><h3 class="section-heading">Position Information</h3><div class="form-grid">'+field('position','Selected Position','text','readonly')+field('department','Department','text','readonly')+'</div><h3 class="section-heading">Personal Information</h3><div class="form-grid">'+field('fullName','Full Name','text','required maxlength="150"')+field('email','Email','email','required maxlength="150"')+field('contactNumber','Contact Number','tel','required maxlength="20"')+'<label>Address<textarea name="address" required maxlength="1000"></textarea></label></div><h3 class="section-heading">Professional Information</h3><div class="form-grid">'+select('education','Educational Attainment',education,'required')+field('specialization','Degree / Specialization','text','required maxlength="150"')+prc()+field('experience','Teaching Experience (years)','number','required min="0" max="60" step="1"')+'</div><h3 class="section-heading">Documents</h3><div class="form-grid">'+upload('resume','Resume / CV')+upload('letter','Application Letter')+upload('transcript','Transcript of Records')+upload('credentials','Supporting Credentials (optional)',False,True)+'</div>','Submit Application')+'</section></div><section class="demo-panel confirmation-panel" id="application-confirmation" tabindex="-1" role="status" hidden><div class="confirmation-icon" aria-hidden="true">✓</div><h2>Application Submitted</h2><p><strong id="confirmation-position"></strong></p><p>Application reference: <strong id="confirmation-id"></strong></p></section>'
make('job-application.html','Job Application Form',application,'application')
side='<aside class="demo-sidebar hrms-sidebar" id="portal-sidebar"><p class="account-name">Faculty Portal<br></p><nav aria-label="Faculty dashboard"><a href="#profile">Faculty Profile</a><a href="#schedule">Teaching Schedule</a><a href="#subjects">Assigned Subjects</a><a href="#rooms">Assigned Classrooms</a><a href="#attendance">Attendance Overview</a><a href="#announcements">Announcements</a></nav></aside>'
faculty='<header class="demo-heading"><h1>Faculty Dashboard</h1><p>Teaching assignments and faculty information.</p></header><section class="demo-panel" id="profile"><h2>Faculty Profile</h2><dl class="profile-grid">'+''.join('<div><dt>'+label+'</dt><dd id="'+id+'">—</dd></div>' for label,id in [('Faculty Name','faculty-name'),('Faculty ID','faculty-id'),('Department','faculty-department'),('Position','faculty-position'),('Employment Status','faculty-status')])+'</dl></section><section class="demo-panel" id="schedule"><h2>Weekly Teaching Schedule</h2><div class="demo-table-wrap"><table><thead><tr>'+''.join('<th scope="col">'+h+'</th>' for h in ['Day','Subject','Time','Section','Assigned Room'])+'</tr></thead><tbody id="schedule-rows"></tbody></table></div></section><section class="demo-panel" id="subjects"><h2>Assigned Subjects</h2><div class="subject-cards" id="subject-cards"></div></section><section class="demo-panel" id="rooms"><h2>Assigned Classrooms</h2><div class="subject-cards" id="room-cards"></div></section><section id="attendance"><h2>Attendance Overview</h2><div class="faculty-overview">'+''.join(panel(label,'<strong>'+value+'</strong>') for label,value in [('Today’s Attendance','Present'),('Time In','08:02 AM'),('Time Out','Pending'),('Days Present','18')])+'</div>'+panel('Attendance History','<div class="demo-table-wrap"><table><thead><tr><th>Date</th><th>Time In</th><th>Time Out</th><th>Status</th></tr></thead><tbody><tr><td>October 26, 2026</td><td>07:55 AM</td><td>05:02 PM</td><td>Present</td></tr><tr><td>October 23, 2026</td><td>08:01 AM</td><td>05:00 PM</td><td>Present</td></tr><tr><td>October 22, 2026</td><td>07:50 AM</td><td>05:05 PM</td><td>Present</td></tr></tbody></table></div>')+'</section><section class="demo-panel" id="announcements"><h2>Announcements</h2><ul class="announcements"><li><strong>Faculty orientation</strong><br>Review your department’s orientation schedule and teaching preparations.</li><li><strong>Teaching schedules</strong><br>Coordinate classroom and schedule concerns with your department.</li><li><strong>HR documents</strong><br>Keep your employee profile and supporting credentials up to date.</li></ul></section>'
make('faculty-dashboard.html','Faculty Dashboard',faculty,'faculty',sidebar=side)
# Reuse the existing Applicant Management page instead of adding a duplicate.
p=root/'hr-admin/HR_Admin_Applicant_Management.html';s=p.read_text(encoding='utf-8')
management='<header class="demo-heading"><h1>Applicant Management</h1><p>Review applications, faculty appointments, and teaching assignments.</p></header>'+message+'<section class="demo-panel"><div class="panel-title"><h2>Applications</h2><button type="button" class="demo-button" id="reset-applicants">Reset List</button></div><div class="flow-toolbar"><input type="search" id="applicant-search" aria-label="Search applicants" placeholder="Search name, department, or email"><select id="applicant-filter" aria-label="Filter application status"><option value="">All statuses</option>'+''.join('<option>'+v+'</option>' for v in ['Submitted','Under Review','Approved','Rejected','Registered as Faculty'])+'</select></div><p id="applicant-count" aria-live="polite"></p><div class="demo-table-wrap"><table><thead><tr>'+''.join('<th scope="col">'+v+'</th>' for v in ['Applicant','Position','Status','Role','Submitted','Action'])+'</tr></thead><tbody id="applicant-rows"></tbody></table></div></section><section class="demo-panel" id="application-detail" tabindex="-1" hidden></section>'
s=re.sub(r'(<main\b[^>]*id="main-content"[^>]*>).*?(</main>)',lambda m:m.group(1)+management+m.group(2),s,count=1,flags=re.S)
s=s.replace('<body>','<body class="flow-page" data-flow="management">')
if '../assets/ui-model.js' not in s:s=s.replace('</head>','<link rel="stylesheet" href="../assets/ui-flow.css"><script defer src="../assets/ui-model.js"></script><script defer src="../assets/ui-flow.js"></script></head>')
s=s.replace('href="../applicant/Applicant_Job_Listings.html"','href="../job-vacancies.html"').replace('href="../authentication/Authentication_Login.html"','href="../login.html"')
p.write_text(s,encoding='utf-8')
# Retain every HR dashboard component; only apply requested palette and updated navigation.
for name in ['DNSC_HR_Dashboard.html','hr-admin/HR_Admin_Dashboard.html']:
 p=root/name;s=p.read_text(encoding='utf-8');prefix='../' if '/' in name else ''
 s=s.replace('class="original-dashboard"','class="original-dashboard flow-page"')
 if prefix+'assets/ui-flow.css' not in s:s=s.replace('</head>','<link rel="stylesheet" href="'+prefix+'assets/ui-flow.css"></head>')
 s=s.replace('href="'+prefix+'applicant/Applicant_Job_Listings.html"','href="'+prefix+'job-vacancies.html"').replace('href="'+prefix+'authentication/Authentication_Login.html"','href="'+prefix+'login.html"')
 s=re.sub(r'<a\b[^>]*href="(?:\.\./)?(?:authentication/)?Authentication_Applicant_Registration\.html"[^>]*>\s*Register\s*</a>','',s)
 p.write_text(s,encoding='utf-8')
# Point homepage entry actions into the new walkthrough without changing its layout.
p=root/'index.html';s=p.read_text(encoding='utf-8');s=s.replace('href="applicant/Applicant_Job_Listings.html"','href="job-vacancies.html"').replace('href="authentication/Authentication_Login.html"','href="login.html"')
if 'assets/ui-flow.css' not in s:s=s.replace('</head>','<link rel="stylesheet" href="assets/ui-flow.css"></head>')
s=s.replace('<body>','<body class="flow-page">');p.write_text(s,encoding='utf-8')
# Applicant self-registration has been removed; old URLs use the sign-in interface.
for entry in [*root.glob('*.html'),*root.glob('*/*.html')]:
    if entry.parent.name=='dist':continue
    content=entry.read_text(encoding='utf-8')
    reg=r'<a\b[^>]*href="(?:\.\./)?(?:authentication/)?Authentication_Applicant_Registration\.html"[^>]*>.*?</a>'
    content=re.sub(r'<li>\s*'+reg+r'\s*</li>','',content,flags=re.S)
    content=re.sub(reg,'',content,flags=re.S)
    entry.write_text(content,encoding='utf-8')

# Deployment copy includes the existing pages and the five requested new pages.
files=list(root.glob('*.html'))
for folder in ['authentication','applicant','employee','hr-admin','student']:files+=list((root/folder).glob('*.html'))
for p in files:
 dest=root/'dist'/p.relative_to(root);dest.parent.mkdir(parents=True,exist_ok=True);shutil.copy2(p,dest)
for asset in ['ui-model.js','ui-flow.js','ui-flow.css','navigation.css']:
 dest=root/'dist/assets'/asset;dest.parent.mkdir(parents=True,exist_ok=True);shutil.copy2(root/'assets'/asset,dest)
with zipfile.ZipFile(root/'static-site.zip','w',zipfile.ZIP_DEFLATED) as archive:
 for p in files:archive.write(p,p.relative_to(root).as_posix())
 for asset in ['ui-model.js','ui-flow.js','ui-flow.css','navigation.css']:archive.write(root/'assets'/asset,'assets/'+asset)
print('Created five frontend flow pages; updated existing login aliases and Applicant Management; preserved HR dashboard components.')
