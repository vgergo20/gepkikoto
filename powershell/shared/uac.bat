@echo off

IF NOT DEFINED ADMIN_RESTARTED (
    set ADMIN_RESTARTED=1
    powershell -Command "Start-Process -FilePath 'Z:\uac.bat' -Verb RunAs"
    EXIT /B
)

reg add "HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\Policies\System" /v EnableLUA /t REG_DWORD /d 0 /f
reg add "HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\Policies\System" /v ConsentPromptBehaviorAdmin /t REG_DWORD /d 0 /f
shutdown /r /t 5 /f

EXIT /B