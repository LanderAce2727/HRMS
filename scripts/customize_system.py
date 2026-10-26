# Archived frontend generator: live pages are maintained directly.
raise SystemExit("This generator is retired. Edit the live HTML, assets/hrms.js, and API modules directly.")
"""Apply supplied page designs and system-facing copy after generating pages."""
from pathlib import Path
import re

ROOT=Path(__file__).resolve().parents[1]
login=(ROOT/'templates/login.source').read_text(encoding='utf-8')
for name in ['Campus Sign In.html','DNSC_Sign_In_Single_Card.html','authentication/Authentication_Login.html','authentication/Authentication_Login_Single_Card.html']:
    prefix='../' if '/' in name else ''
    s=login
    s=s.replace('<button class="theme-btn" id="themeBtn"',f'<a class="login-attendance" href="{prefix}Attendance.html">Attendance</a><button class="theme-btn" id="themeBtn"',1)
    s=re.sub(r"  \$\('signinForm'\)\.addEventListener\('submit', function \(e\) \{.*?\n  \}\);",lambda m:"""  $('signinForm').addEventListener('submit', function (e) {
    e.preventDefault();
    window.location.href = '"""+prefix+"""' + (role === 'admin' ? 'hr-admin/HR_Admin_Dashboard.html' : 'Attendance.html');
  });""",s,count=1,flags=re.S)
    s=s.replace('Continue with Gmail','Continue as applicant').replace('Use the Gmail address you applied with.','Browse vacancies and manage your applications.')
    s=s.replace('Applicants sign in with Google to check their application.','Browse vacancies and manage your applications.')
    s=s.replace('Sign in with the work email HR gave you.','Access your faculty attendance and employee information.')
    s=s.replace('Restricted to HR staff. Use your admin credentials.','Manage employee records, attendance, and payroll.')
    s=s.replace('Instructor accounts are created when HR approves your application.','Welcome to the DNSC Faculty Portal.').replace('Admin access is limited to authorized HR staff.','Welcome to the DNSC HR Administration Portal.')
    s=s.replace('<title>Campus Sign In</title>','<title>Sign in | DNSC Smart HRMS</title>')
    s=s.replace('</head>',f'<link rel="stylesheet" href="{prefix}assets/demo.css"></head>',1)
    s=s.replace('</body>',f'<script src="{prefix}assets/demo.js"></script></body>',1)
    (ROOT/name).write_text(s,encoding='utf-8')

s=(ROOT/'templates/attendance.source').read_text(encoding='utf-8')
s=re.sub(r'<p class="footnote">Prototype only:.*?</p>','',s,flags=re.S)
s=s.replace('· Demo','').replace('recorded in preview at','recorded at').replace('>Preview</span>','>Recorded</span>').replace('Recorded in this preview','Recorded today')
s=s.replace("localStorage.setItem('dnsc-faculty-theme',mode)","localStorage.setItem('dnsc-demo-theme',mode)")
# The shared theme controller owns initial preference; do not overwrite it on load.
s=s.replace("localStorage.getItem('dnsc-faculty-theme')","localStorage.getItem('dnsc-demo-theme')")
s=s.replace("setTheme(savedTheme==='dark'?'dark':'light');","root.setAttribute('data-theme',savedTheme==='dark'?'dark':'light');")
s=s.replace('<div class="scan-feedback"','<div role="status" class="scan-feedback"')
s=s.replace('</head>','<link rel="stylesheet" href="assets/demo.css"></head>',1).replace('</body>','<script src="assets/demo.js"></script></body>',1)
(ROOT/'Attendance.html').write_text(s,encoding='utf-8')

replacements={
 'All 28 pages are ready, plus an alternative login. Choose any page to explore the frontend demo — no sign-in required.':'Access recruitment, employee services, attendance, and faculty evaluation.',
 'Frontend demonstration':'DNSC Smart HRMS', 'Sample data · Open access':'Human Resource Management',
 'Preview the verification step without an email or code requirement.':'Verify your account and continue to your portal.',
 'Join the DNSC applicant portal. All fields are optional in this demo.':'Join the DNSC applicant portal.',
 'Access the faculty evaluation demo.':'Access faculty evaluation.',
 'Choose a faculty member to preview the evaluation form. The form uses sample course details.':'Select a faculty member to evaluate.',
 'Enter optional student details to preview the evaluation flow.':'Enter your student and enrollment details.',
 'The evaluation demo flow is complete.':'Thank you for completing your faculty evaluation.',
 'Submission preview complete':'Evaluation complete', 'Demo confirmation':'Completed',
 'No evaluation was sent or stored. You can explore another evaluation or return to the page directory.':'You may continue to another faculty evaluation or return to your workspace.',
 'The demo is open: continue directly to faculty selection.':'Continue to faculty selection.',
 'Review sample applications. Decisions reset when the page reloads.':'Review applications and update their status.',
 'HR correction controls are open for demo access. Changes reset on reload.':'Review attendance logs and correct time entries.',
 'Sample figures only. This is not an official payslip.':'Review your pay period, earnings, and deductions.',
 'Sample payroll figures for demonstration only.':'Review earnings, deductions, and payroll totals.',
 'Try the application flow. Nothing is uploaded or saved.':'Provide your details and supporting documents.',
 'Optional demo code':'Verification code', 'Demo only — do not use a real password':'Enter a password',
 'Sample data':'', 'Frontend preview':'DNSC Smart HRMS',
 'Your demo application preview is complete.':'Your application is ready for review.',
 'Sample status: the HR office is reviewing credentials.':'The HR office is reviewing credentials.',
 'Prototype dashboard · Demo data':'DNSC administration',
 'Exports contain demo records only and are generated locally in your browser.':'Download records in CSV format.',
 'Sample payroll processed for preview. No payment was made.':'Payroll register prepared.',
 'Preview action completed. No data was stored.':'Action completed.',
 'Demo only':'Current period',
}
paths=[ROOT/'index.html',ROOT/'DNSC_HR_Dashboard.html',ROOT/'Attendance.html']
for folder in ['authentication','hr-admin','applicant','employee','student']:
    paths.extend((ROOT/folder).glob('*.html'))
for p in paths:
    s=p.read_text(encoding='utf-8')
    s=s.replace('../employee/Employee_Attendance_Check-In.html','../Attendance.html').replace('employee/Employee_Attendance_Check-In.html','Attendance.html')
    s=re.sub(r'<div class="notice">Interactive frontend prototype\..*?</div>','',s,flags=re.S)
    for a,b in replacements.items():s=s.replace(a,b)
    def wording(m):
        text=m.group(1)
        text=re.sub(r'\b[Ss]ample\s+','',text)
        text=re.sub(r'\s*·\s*sample\b','',text)
        text=re.sub(r'\b[Dd]emo\s+','',text)
        text=text.replace('Preview submission','Submit').replace('Preview registration','Create account').replace('Preview profile update','Update profile').replace('Preview employee record','Apply changes').replace('Preview vacancy','Review vacancy').replace('Preview payslip','View payslip').replace('>','')
        if text.strip()=='Preview':text='View details'
        return '>'+text+'<'
    # Keep identifiers, CSS, and executable code intact when editing visible text.
    chunks=re.split(r'(<script\b[^>]*>.*?</script>|<style\b[^>]*>.*?</style>)',s,flags=re.S|re.I)
    s=''.join(chunk if re.match(r'<(?:script|style)\b',chunk,re.I) else re.sub(r'>([^<>]+)<',wording,chunk) for chunk in chunks)
    # Strings emitted by the original dashboard renderer.
    s=s.replace(' · sample','').replace('2026 sample period','2026 pay period').replace('Demo records','Records').replace('Sample entries','Entries').replace('Sample faculty records','Faculty records').replace('(Demo only)','').replace('_demo.csv','_records.csv')
    if p.name=='DNSC_HR_Dashboard.html':
        if 'assets/demo.css' not in s:s=s.replace('</head>','<link rel="stylesheet" href="assets/demo.css"></head>',1)
        if 'assets/demo.js' not in s:s=s.replace('</body>','<script src="assets/demo.js"></script></body>',1)
        s=s.replace("$('pendingNav').textContent=pending;","if($('pendingNav'))$('pendingNav').textContent=pending;")
        s=s.replace("let state;try{state=JSON.parse(localStorage.getItem('dnsc_hr_demo_v1'))||structuredClone(defaults)}catch(e){state=JSON.parse(JSON.stringify(defaults))}","let state=JSON.parse(JSON.stringify(defaults));")
        s=s.replace("save=()=>{try{localStorage.setItem('dnsc_hr_demo_v1',JSON.stringify(state))}catch(e){}}","save=()=>{}")
    p.write_text(s,encoding='utf-8')
print('Applied supplied login and attendance designs, system copy, and shared navigation.')
# Preserve the maintained integration notes when regenerating pages.

# Retain the UI/UX improvements after a full page rebuild.
import runpy
runpy.run_path(str(ROOT / "scripts" / "improve_ui.py"))
