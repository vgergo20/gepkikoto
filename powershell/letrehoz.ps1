param(
    [Parameter(Mandatory=$true)]
    [string]$VM_NEV
)
$VBoxManage = "C:\Program Files\Oracle\VirtualBox\VBoxManage.exe"
$VM = "$VM_NEV"
$MemoryMB = 4096
$CPUCores = 2
$DiskSizeMB = 20000

$UserName = "User"
$Password = "1234"
$Locale = "hu_HU"
$Country = "HU"
$ProductKey = "TX9XD-98N7V-6WMQ6-BX7FG-H8Q99" 

$ISOPath = "C:\xampp\htdocs\Windows.iso"
$Shared= "C:\xampp\htdocs\files\shared"

& $VBoxManage createvm --name $VM --ostype "Windows10_64" --register

& $VBoxManage createhd --filename /VirtualBox/$VM/$VM.vdi --size 32768
& $VBoxManage storagectl $VM --name "SATA Controller" --add sata --controller IntelAHCI
& $VBoxManage storageattach $VM --storagectl "SATA Controller" --port 0 --device 0 --type hdd --medium /VirtualBox/$VM/$VM.vdi
& $VBoxManage storagectl $VM --name "IDE Controller" --add ide
& $VBoxManage storageattach $VM --storagectl "IDE Controller" --port 0 --device 0 --type dvddrive --medium $ISOPath

& $VBoxManage modifyvm $VM --ioapic on
& $VBoxManage modifyvm $VM --boot1 dvd --boot2 disk --boot3 none --boot4 none
& $VBoxManage modifyvm $VM --memory 4096 --vram 128
& "$VBoxManage" sharedfolder add "$VM" --name "Shared_Files" --hostpath "$Shared" --automount

& $VBoxManage unattended install $VM --key=$ProductKey --iso=$ISOPath --user=User --full-user-name=User --password="1234" --install-additions --time-zone=CET

& $VBoxManage startvm $VM --type gui
Start-Sleep -Seconds 1000
& $VBoxManage controlvm $VM reset
Start-Sleep -Seconds 60

& $VBoxManage guestcontrol "$VM" run --exe "C:\Windows\System32\cmd.exe" --username "User" --password "1234" -- " /c Z:\uac.bat"
Start-Sleep -Seconds 240

#VÁLTOZÓK
$AnyDeskDownloadURL = "https://download.anydesk.com/AnyDesk.exe"
$GuestDestination = "C:\Users\$UserName\Downloads\AnyDesk.exe"
$GuestScriptPath = "Z:\anydesk.bat"
$AnyDeskExePath = "C:\Program Files (x86)\AnyDesk\AnyDesk.exe"
$OutputFile = "Z:\$VM.txt"

#LETÖLTÉS
$DownloadCommand = "powershell.exe -ExecutionPolicy Bypass -Command `"Invoke-WebRequest -Uri '$AnyDeskDownloadURL' -OutFile '$GuestDestination' -Headers @{ 'User-Agent' = 'Mozilla/5.0' }`""
& $VBoxManage guestcontrol "$VM" run --exe "cmd.exe" --username "$UserName" --password "$Password" -- "/c $DownloadCommand"
Start-Sleep -Seconds 5

#TELEPÍTÉS
$RunCmd = "powershell.exe -ExecutionPolicy Bypass -Command `"Start-Process -FilePath `"$GuestScriptPath`" -Verb RunAs -Wait`""
& $VBoxManage guestcontrol "$VM" run --exe "cmd.exe" --username "$UserName" --password "$Password" -- "/c $RunCmd"

#ID KINYERÉSE
$IDCommand = "cmd.exe /c `"$AnyDeskExePath`" --get-id > `"$OutputFile`""
$EncodedCommand = [Convert]::ToBase64String([System.Text.Encoding]::Unicode.GetBytes($IDCommand))
$Runcmdid = "powershell.exe -EncodedCommand `"$EncodedCommand`""
& $VBoxManage guestcontrol "$VM" run --exe "cmd.exe" --username "$UserName" --password "$Password" -- "/c $Runcmdid"

Start-Sleep -Seconds 60
& $VBoxManage controlvm $VM poweroff

#Jelzés a weboldalnak hogy lefutott a script
$baseURL = "http://localhost/update-status.php"

$updateURL = "$baseURL" + "?gep_azonosito=" + $VM_NEV

try {
    $webClient = New-Object System.Net.WebClient
    $webClient.DownloadString($updateURL) | Out-Null
} catch {
    Write-Error "Hiba az adatbazis frissitese soran: $($_.Exception.Message)"
}