# Archived frontend generator: live pages are maintained directly.
raise SystemExit("This generator is retired. Edit the live HTML, assets/hrms.js, and API modules directly.")
"""Keep targeted UI copy updates reproducible after the existing page generator."""
from pathlib import Path
import re
ROOT=Path(__file__).resolve().parents[1]
paths=[*ROOT.glob('*.html')]
for folder in ['authentication','applicant','hr-admin','employee','student']:
    paths.extend((ROOT/folder).glob('*.html'))
for p in paths:
    s=p.read_text(encoding='utf-8')
    # Repair existing UTF-8 text that was previously decoded as Windows-1252.
    for bad,good in [('Â·','·'),('â‚±','₱'),('â€™','’'),('â€“','–'),('â€”','—')]:s=s.replace(bad,good)
    if 'signinForm' in s:
        s=s.replace('Apply as instructor','Apply for Open Positions').replace('Apply to <span>teach</span>','Apply for <span>open positions</span>')
        s=s.replace('Teach with us. Apply in minutes.','Explore teaching and non-teaching opportunities.')
        s=s.replace('Create an account with Gmail, send your details and resume, and follow your status until HR replies.','Browse open positions and preview the application process.')
        s=s.replace('>Sign in As<','>Preview a portal<').replace('>Sign in</button>','>Preview portal</button>')
        s=s.replace('Keep me signed in','Remember me (unavailable in prototype)')
        s=s.replace('id="remember"','id="remember" disabled') if 'id="remember" disabled' not in s else s
        s=s.replace('Continue as applicant','Browse Job Vacancies')
        s=s.replace('Create an account with Gmail','Preview applicant registration')
        s=s.replace('Browse vacancies and manage your applications.','Browse Job Vacancies. Account access is not connected yet.')
        s=s.replace('<title>Sign in |','<title>Login preview |')
    s=s.replace('Admin account</small>','Portal preview</small>')
    if p.name in ['Authentication_Applicant_Registration.html','Authentication_Student_Registration.html']:
        s=re.sub(r'(>\s*)Create account(\s*</button>)',r'\1Preview registration\2',s)
        s=s.replace('Join the DNSC applicant portal.','Preview applicant registration. No account is created and no email is sent.')
    if p.name=='Authentication_Account_Verification.html':
        s=s.replace('Verify your account and continue to your portal.','Account verification is not connected. No code is sent or validated in this prototype.')
        s=s.replace('<h2>Verify your account</h2>','<h2>Verification preview</h2>')
        s=s.replace('Continue to applicant portal','Browse Job Vacancies')
    if p.name=='HR_Admin_Job_Vacancies.html':
        s=s.replace('Job vacancies','Job Vacancies')
        slugs=iter(['instructor-i','part-time-lecturer','administrative-assistant'])
        s=re.sub(r'href="../applicant/Applicant_Job_Details.html"',lambda m:'href="../applicant/Applicant_Job_Details.html?job='+next(slugs)+'"',s)
    if p.name=='Applicant_Job_Listings.html':
        s=s.replace('Find your next opportunity','Job Vacancies')
        s=s.replace('Explore opportunities at DNSC.','Explore teaching and non-teaching opportunities at DNSC. These listings are prototype examples.')
        slugs=['instructor-i','part-time-lecturer','administrative-assistant']
        i=[0]
        def joblinks(m):
            slug=slugs[i[0]];i[0]+=1
            return f'<div class="demo-actions"><a class="demo-button primary" href="Applicant_Application_Form.html?job={slug}">Apply Now</a><a class="demo-button" href="Applicant_Job_Details.html?job={slug}">View job details</a></div>'
        s=re.sub(r'<a class="demo-button" href="../applicant/Applicant_Job_Details.html">View job details</a>',joblinks,s)
        for dept,category in [('Information Technology','Teaching'),('Education','Teaching'),('Human Resources','Non-teaching')]:
            s=s.replace(f'data-status>{dept}</span>',f'data-status>{category} · {dept}</span>')
    if p.name=='Applicant_Application_Form.html':
        s=s.replace('Apply for a position','Job Application Form').replace('Provide your details and supporting documents.','Preview your application for the selected position. No documents are uploaded or saved.')
        if 'data-position-summary' not in s:
            s=s.replace('<h2>Application details</h2>','<h2>Application details</h2><p data-position-summary aria-live="polite"></p>')
        s=s.replace('<select>','<select id="applicationPosition" name="position">',1)
        s=s.replace('>Submit</button>','>Preview Application</button>')
    if p.name=='Attendance.html':s=s.replace('id="feedback" role="status"','id="feedback"')
    if p.name=='index.html':
        s=s.replace('DNSC Smart HRMS | All pages','DNSC Smart HRMS | Home')
        s=s.replace('<header class="demo-heading"><h1>Explore Smart HRMS</h1><p>Access recruitment, employee services, attendance, and faculty evaluation.</p></header>', '<header class="hrms-hero"><span class="demo-badge">Davao del Norte State College</span><h1>Welcome to DNSC Smart HRMS</h1><p>Explore career opportunities and access human resource services, attendance, and faculty evaluation in one place.</p><div class="demo-actions"><a class="demo-button primary" href="applicant/Applicant_Job_Listings.html">Apply for Open Positions</a><a class="demo-button" href="authentication/Authentication_Login.html">Login</a></div></header><h2>Explore the prototype pages</h2>')
        s=s.replace('Find your next opportunity','Job Vacancies').replace('Apply for a position','Job Application Form').replace('<h2>Hr Admin</h2>','<h2>HR Admin</h2>')
        s=re.sub(r'<div class="demo-brand">.*?</small></div></div>', '',s,count=1)
    p.write_text(s,encoding='utf-8')
print('Updated shared UI copy, public homepage, and recruitment navigation.')
