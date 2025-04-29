output "instance_ip" {
  description = "Public IP of the Laravel VM instance"
  value       = google_compute_instance.laravel_vm.network_interface[0].access_config[0].nat_ip
}
