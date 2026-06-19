param(
    [Parameter(Mandatory=$true)]
    [string]$VM_NEV
)

$VBoxName = $VM_NEV
$VBoxManagePath = "C:\Program Files\Oracle\VirtualBox\VBoxManage.exe"

#Leállítom ha fut
& $VBoxManagePath controlvm "$VBoxName" poweroff 2>$null 

Start-Sleep -Seconds 5

#Törlök minden fájlt 
& $VBoxManagePath unregistervm "$VBoxName" --delete 2>$null
$FilePath = "C:\xampp\htdocs\files\shared\$VBoxName.txt"
Remove-Item $FilePath -Force