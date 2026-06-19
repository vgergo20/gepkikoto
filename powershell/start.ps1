param(
    [Parameter(Mandatory=$true)]
    [string]$VM_NEV
)

$VBoxManagePath = "C:\Program Files\Oracle\VirtualBox\VBoxManage.exe"
$VMName = $VM_NEV
& $VBoxManagePath startvm $VMName --type gui