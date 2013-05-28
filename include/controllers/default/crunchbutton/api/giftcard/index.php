<?php

class Controller_api_Giftcard extends Crunchbutton_Controller_Rest {
	public function init() {

		$test = 'TzoxNzoiQ2FuYV9TdXBlckNsb3N1cmUiOjI6e3M6NzoiACoAY29kZSI7czo1MjoiZnVuY3Rpb24oKSB1c2UoICRnaWZ0ICkgewoJCQkkZ2lmdC0+bm90aWZ5U01TKCk7CgkJfSI7czoxNzoiACoAdXNlZF92YXJpYWJsZXMiO2E6MTp7czo0OiJnaWZ0IjtPOjE4OiJDcnVuY2hidXR0b25fUHJvbW8iOjc6e3M6MTg6IgBDYW5hX1RhYmxlAF90YWJsZSI7czo1OiJwcm9tbyI7czoxOToiAENhbmFfVGFibGUAX2lkX3ZhciI7czo4OiJpZF9wcm9tbyI7czoxOToiAENhbmFfVGFibGUAX2ZpZWxkcyI7TjtzOjE1OiIAQ2FuYV9UYWJsZQBfZGIiO086NzoiQ2FuYV9EYiI6Mzp7czoxNDoiAENhbmFfRGIAX3R5cGUiO3M6NToiTXlTUUwiO3M6MTM6IgBDYW5hX0RiAF9kYm8iO086MTY6IkNhbmFfRGJfTXlTUUxfRGIiOjI3OntzOjEzOiJhZmZlY3RlZF9yb3dzIjtOO3M6MTE6ImNsaWVudF9pbmZvIjtOO3M6MTQ6ImNsaWVudF92ZXJzaW9uIjtOO3M6MTM6ImNvbm5lY3RfZXJybm8iO047czoxMzoiY29ubmVjdF9lcnJvciI7TjtzOjU6ImVycm5vIjtOO3M6NToiZXJyb3IiO047czoxMDoiZXJyb3JfbGlzdCI7TjtzOjExOiJmaWVsZF9jb3VudCI7TjtzOjk6Imhvc3RfaW5mbyI7TjtzOjQ6ImluZm8iO047czo5OiJpbnNlcnRfaWQiO047czoxMToic2VydmVyX2luZm8iO047czoxNDoic2VydmVyX3ZlcnNpb24iO047czo0OiJzdGF0IjtOO3M6ODoic3Fsc3RhdGUiO047czoxNjoicHJvdG9jb2xfdmVyc2lvbiI7TjtzOjk6InRocmVhZF9pZCI7TjtzOjEzOiJ3YXJuaW5nX2NvdW50IjtOO3M6MjM6IgBDYW5hX0RiX015U1FMX0RiAF9ob3N0IjtOO3M6MjM6IgBDYW5hX0RiX015U1FMX0RiAF91c2VyIjtOO3M6MjM6IgBDYW5hX0RiX015U1FMX0RiAF9wYXNzIjtOO3M6MjE6IgBDYW5hX0RiX015U1FMX0RiAF9kYiI7TjtzOjI2OiIAQ2FuYV9EYl9NeVNRTF9EYgBfcXVlcmllcyI7TjtzOjIzOiIAQ2FuYV9EYl9NeVNRTF9EYgBfY29ubiI7TjtzOjMxOiIAQ2FuYV9EYl9NeVNRTF9EYgBfc3RvcmVkRmllbGRzIjthOjI6e3M6NDoidXNlciI7YToxNzp7aTowO086ODoic3RkQ2xhc3MiOjY6e3M6NToiRmllbGQiO3M6NzoiaWRfdXNlciI7czo0OiJUeXBlIjtzOjE2OiJpbnQoMTEpIHVuc2lnbmVkIjtzOjQ6Ik51bGwiO2I6MDtzOjM6IktleSI7czozOiJQUkkiO3M6NzoiRGVmYXVsdCI7TjtzOjU6IkV4dHJhIjtzOjE0OiJhdXRvX2luY3JlbWVudCI7fWk6MTtPOjg6InN0ZENsYXNzIjo2OntzOjU6IkZpZWxkIjtzOjY6ImFjdGl2ZSI7czo0OiJUeXBlIjtzOjEwOiJ0aW55aW50KDEpIjtzOjQ6Ik51bGwiO2I6MDtzOjM6IktleSI7czowOiIiO3M6NzoiRGVmYXVsdCI7czoxOiIxIjtzOjU6IkV4dHJhIjtzOjA6IiI7fWk6MjtPOjg6InN0ZENsYXNzIjo2OntzOjU6IkZpZWxkIjtzOjQ6Im5hbWUiO3M6NDoiVHlwZSI7czoxMjoidmFyY2hhcigyNTUpIjtzOjQ6Ik51bGwiO2I6MTtzOjM6IktleSI7czowOiIiO3M6NzoiRGVmYXVsdCI7TjtzOjU6IkV4dHJhIjtzOjA6IiI7fWk6MztPOjg6InN0ZENsYXNzIjo2OntzOjU6IkZpZWxkIjtzOjU6ImVtYWlsIjtzOjQ6IlR5cGUiO3M6MTI6InZhcmNoYXIoMjU1KSI7czo0OiJOdWxsIjtiOjE7czozOiJLZXkiO3M6MDoiIjtzOjc6IkRlZmF1bHQiO047czo1OiJFeHRyYSI7czowOiIiO31pOjQ7Tzo4OiJzdGRDbGFzcyI6Njp7czo1OiJGaWVsZCI7czo1OiJwaG9uZSI7czo0OiJUeXBlIjtzOjEyOiJ2YXJjaGFyKDI1NSkiO3M6NDoiTnVsbCI7YjoxO3M6MzoiS2V5IjtzOjA6IiI7czo3OiJEZWZhdWx0IjtOO3M6NToiRXh0cmEiO3M6MDoiIjt9aTo1O086ODoic3RkQ2xhc3MiOjY6e3M6NToiRmllbGQiO3M6OToic3RyaXBlX2lkIjtzOjQ6IlR5cGUiO3M6MTI6InZhcmNoYXIoMjU1KSI7czo0OiJOdWxsIjtiOjE7czozOiJLZXkiO3M6MDoiIjtzOjc6IkRlZmF1bHQiO047czo1OiJFeHRyYSI7czowOiIiO31pOjY7Tzo4OiJzdGRDbGFzcyI6Njp7czo1OiJGaWVsZCI7czo3OiJhZGRyZXNzIjtzOjQ6IlR5cGUiO3M6NDoidGV4dCI7czo0OiJOdWxsIjtiOjE7czozOiJLZXkiO3M6MDoiIjtzOjc6IkRlZmF1bHQiO047czo1OiJFeHRyYSI7czowOiIiO31pOjc7Tzo4OiJzdGRDbGFzcyI6Njp7czo1OiJGaWVsZCI7czo0OiJjYXJkIjtzOjQ6IlR5cGUiO3M6MTE6InZhcmNoYXIoMTYpIjtzOjQ6Ik51bGwiO2I6MTtzOjM6IktleSI7czowOiIiO3M6NzoiRGVmYXVsdCI7TjtzOjU6IkV4dHJhIjtzOjA6IiI7fWk6ODtPOjg6InN0ZENsYXNzIjo2OntzOjU6IkZpZWxkIjtzOjEzOiJkZWxpdmVyeV90eXBlIjtzOjQ6IlR5cGUiO3M6MjY6ImVudW0oJ3Rha2VvdXQnLCdkZWxpdmVyeScpIjtzOjQ6Ik51bGwiO2I6MTtzOjM6IktleSI7czowOiIiO3M6NzoiRGVmYXVsdCI7TjtzOjU6IkV4dHJhIjtzOjA6IiI7fWk6OTtPOjg6InN0ZENsYXNzIjo2OntzOjU6IkZpZWxkIjtzOjk6ImNhcmRfdHlwZSI7czo0OiJUeXBlIjtzOjQzOiJlbnVtKCd2aXNhJywnbWFzdGVyY2FyZCcsJ2FtZXgnLCdkaXNjb3ZlcicpIjtzOjQ6Ik51bGwiO2I6MTtzOjM6IktleSI7czowOiIiO3M6NzoiRGVmYXVsdCI7TjtzOjU6IkV4dHJhIjtzOjA6IiI7fWk6MTA7Tzo4OiJzdGRDbGFzcyI6Njp7czo1OiJGaWVsZCI7czo0OiJ1dWlkIjtzOjQ6IlR5cGUiO3M6ODoiY2hhcigzNikiO3M6NDoiTnVsbCI7YjoxO3M6MzoiS2V5IjtzOjM6IlVOSSI7czo3OiJEZWZhdWx0IjtOO3M6NToiRXh0cmEiO3M6MDoiIjt9aToxMTtPOjg6InN0ZENsYXNzIjo2OntzOjU6IkZpZWxkIjtzOjg6InBheV90eXBlIjtzOjQ6IlR5cGUiO3M6MTk6ImVudW0oJ2Nhc2gnLCdjYXJkJykiO3M6NDoiTnVsbCI7YjoxO3M6MzoiS2V5IjtzOjA6IiI7czo3OiJEZWZhdWx0IjtOO3M6NToiRXh0cmEiO3M6MDoiIjt9aToxMjtPOjg6InN0ZENsYXNzIjo2OntzOjU6IkZpZWxkIjtzOjExOiJiYWxhbmNlZF9pZCI7czo0OiJUeXBlIjtzOjEyOiJ2YXJjaGFyKDI1NSkiO3M6NDoiTnVsbCI7YjoxO3M6MzoiS2V5IjtzOjA6IiI7czo3OiJEZWZhdWx0IjtOO3M6NToiRXh0cmEiO3M6MDoiIjt9aToxMztPOjg6InN0ZENsYXNzIjo2OntzOjU6IkZpZWxkIjtzOjEyOiJsb2NhdGlvbl9sYXQiO3M6NDoiVHlwZSI7czo1OiJmbG9hdCI7czo0OiJOdWxsIjtiOjE7czozOiJLZXkiO3M6MDoiIjtzOjc6IkRlZmF1bHQiO047czo1OiJFeHRyYSI7czowOiIiO31pOjE0O086ODoic3RkQ2xhc3MiOjY6e3M6NToiRmllbGQiO3M6MTI6ImxvY2F0aW9uX2xvbiI7czo0OiJUeXBlIjtzOjU6ImZsb2F0IjtzOjQ6Ik51bGwiO2I6MTtzOjM6IktleSI7czowOiIiO3M6NzoiRGVmYXVsdCI7TjtzOjU6IkV4dHJhIjtzOjA6IiI7fWk6MTU7Tzo4OiJzdGRDbGFzcyI6Njp7czo1OiJGaWVsZCI7czoxMzoiY2FyZF9leHBfeWVhciI7czo0OiJUeXBlIjtzOjY6ImludCg0KSI7czo0OiJOdWxsIjtiOjE7czozOiJLZXkiO3M6MDoiIjtzOjc6IkRlZmF1bHQiO047czo1OiJFeHRyYSI7czowOiIiO31pOjE2O086ODoic3RkQ2xhc3MiOjY6e3M6NToiRmllbGQiO3M6MTQ6ImNhcmRfZXhwX21vbnRoIjtzOjQ6IlR5cGUiO3M6NjoiaW50KDIpIjtzOjQ6Ik51bGwiO2I6MTtzOjM6IktleSI7czowOiIiO3M6NzoiRGVmYXVsdCI7TjtzOjU6IkV4dHJhIjtzOjA6IiI7fX1zOjU6InByb21vIjthOjIwOntpOjA7Tzo4OiJzdGRDbGFzcyI6Njp7czo1OiJGaWVsZCI7czo4OiJpZF9wcm9tbyI7czo0OiJUeXBlIjtzOjE2OiJpbnQoMTEpIHVuc2lnbmVkIjtzOjQ6Ik51bGwiO2I6MDtzOjM6IktleSI7czozOiJQUkkiO3M6NzoiRGVmYXVsdCI7TjtzOjU6IkV4dHJhIjtzOjE0OiJhdXRvX2luY3JlbWVudCI7fWk6MTtPOjg6InN0ZENsYXNzIjo2OntzOjU6IkZpZWxkIjtzOjc6ImlkX3VzZXIiO3M6NDoiVHlwZSI7czoxNjoiaW50KDExKSB1bnNpZ25lZCI7czo0OiJOdWxsIjtiOjE7czozOiJLZXkiO3M6MzoiTVVMIjtzOjc6IkRlZmF1bHQiO047czo1OiJFeHRyYSI7czowOiIiO31pOjI7Tzo4OiJzdGRDbGFzcyI6Njp7czo1OiJGaWVsZCI7czo1OiJ2YWx1ZSI7czo0OiJUeXBlIjtzOjU6ImZsb2F0IjtzOjQ6Ik51bGwiO2I6MTtzOjM6IktleSI7czowOiIiO3M6NzoiRGVmYXVsdCI7TjtzOjU6IkV4dHJhIjtzOjA6IiI7fWk6MztPOjg6InN0ZENsYXNzIjo2OntzOjU6IkZpZWxkIjtzOjEzOiJpZF9yZXN0YXVyYW50IjtzOjQ6IlR5cGUiO3M6MTY6ImludCgxMSkgdW5zaWduZWQiO3M6NDoiTnVsbCI7YjoxO3M6MzoiS2V5IjtzOjM6Ik1VTCI7czo3OiJEZWZhdWx0IjtOO3M6NToiRXh0cmEiO3M6MDoiIjt9aTo0O086ODoic3RkQ2xhc3MiOjY6e3M6NToiRmllbGQiO3M6NDoiY29kZSI7czo0OiJUeXBlIjtzOjExOiJ2YXJjaGFyKDUwKSI7czo0OiJOdWxsIjtiOjE7czozOiJLZXkiO3M6MDoiIjtzOjc6IkRlZmF1bHQiO047czo1OiJFeHRyYSI7czowOiIiO31pOjU7Tzo4OiJzdGRDbGFzcyI6Njp7czo1OiJGaWVsZCI7czo0OiJkYXRlIjtzOjQ6IlR5cGUiO3M6ODoiZGF0ZXRpbWUiO3M6NDoiTnVsbCI7YjoxO3M6MzoiS2V5IjtzOjA6IiI7czo3OiJEZWZhdWx0IjtOO3M6NToiRXh0cmEiO3M6MDoiIjt9aTo2O086ODoic3RkQ2xhc3MiOjY6e3M6NToiRmllbGQiO3M6NDoidHlwZSI7czo0OiJUeXBlIjtzOjMwOiJlbnVtKCd1c2VyX3NoYXJlJywnZ2lmdF9jYXJkJykiO3M6NDoiTnVsbCI7YjoxO3M6MzoiS2V5IjtzOjA6IiI7czo3OiJEZWZhdWx0IjtOO3M6NToiRXh0cmEiO3M6MDoiIjt9aTo3O086ODoic3RkQ2xhc3MiOjY6e3M6NToiRmllbGQiO3M6NToicGhvbmUiO3M6NDoiVHlwZSI7czoxMjoidmFyY2hhcigyNTApIjtzOjQ6Ik51bGwiO2I6MTtzOjM6IktleSI7czowOiIiO3M6NzoiRGVmYXVsdCI7TjtzOjU6IkV4dHJhIjtzOjA6IiI7fWk6ODtPOjg6InN0ZENsYXNzIjo2OntzOjU6IkZpZWxkIjtzOjEzOiJlbWFpbF9zdWJqZWN0IjtzOjQ6IlR5cGUiO3M6MTI6InZhcmNoYXIoMjUwKSI7czo0OiJOdWxsIjtiOjE7czozOiJLZXkiO3M6MDoiIjtzOjc6IkRlZmF1bHQiO047czo1OiJFeHRyYSI7czowOiIiO31pOjk7Tzo4OiJzdGRDbGFzcyI6Njp7czo1OiJGaWVsZCI7czoxMzoiZW1haWxfY29udGVudCI7czo0OiJUeXBlIjtzOjQ6InRleHQiO3M6NDoiTnVsbCI7YjoxO3M6MzoiS2V5IjtzOjA6IiI7czo3OiJEZWZhdWx0IjtOO3M6NToiRXh0cmEiO3M6MDoiIjt9aToxMDtPOjg6InN0ZENsYXNzIjo2OntzOjU6IkZpZWxkIjtzOjU6ImVtYWlsIjtzOjQ6IlR5cGUiO3M6MTI6InZhcmNoYXIoMjUwKSI7czo0OiJOdWxsIjtiOjE7czozOiJLZXkiO3M6MDoiIjtzOjc6IkRlZmF1bHQiO047czo1OiJFeHRyYSI7czowOiIiO31pOjExO086ODoic3RkQ2xhc3MiOjY6e3M6NToiRmllbGQiO3M6MTg6ImlkX29yZGVyX3JlZmVyZW5jZSI7czo0OiJUeXBlIjtzOjE2OiJpbnQoMTApIHVuc2lnbmVkIjtzOjQ6Ik51bGwiO2I6MTtzOjM6IktleSI7czozOiJNVUwiO3M6NzoiRGVmYXVsdCI7TjtzOjU6IkV4dHJhIjtzOjA6IiI7fWk6MTI7Tzo4OiJzdGRDbGFzcyI6Njp7czo1OiJGaWVsZCI7czoyMToiaWRfcmVzdGF1cmFudF9wYWlkX2J5IjtzOjQ6IlR5cGUiO3M6MTY6ImludCgxMCkgdW5zaWduZWQiO3M6NDoiTnVsbCI7YjoxO3M6MzoiS2V5IjtzOjM6Ik1VTCI7czo3OiJEZWZhdWx0IjtOO3M6NToiRXh0cmEiO3M6MDoiIjt9aToxMztPOjg6InN0ZENsYXNzIjo2OntzOjU6IkZpZWxkIjtzOjc6InBhaWRfYnkiO3M6NDoiVHlwZSI7czo2NjoiZW51bSgnQ1JVTkNIQlVUVE9OJywnUkVTVEFVUkFOVCcsJ1BST01PVElPTkFMJywnT1RIRVJfUkVTVEFVUkFOVCcpIjtzOjQ6Ik51bGwiO2I6MTtzOjM6IktleSI7czowOiIiO3M6NzoiRGVmYXVsdCI7TjtzOjU6IkV4dHJhIjtzOjA6IiI7fWk6MTQ7Tzo4OiJzdGRDbGFzcyI6Njp7czo1OiJGaWVsZCI7czoxMDoiY3JlYXRlZF9ieSI7czo0OiJUeXBlIjtzOjExOiJ2YXJjaGFyKDUwKSI7czo0OiJOdWxsIjtiOjE7czozOiJLZXkiO3M6MDoiIjtzOjc6IkRlZmF1bHQiO047czo1OiJFeHRyYSI7czowOiIiO31pOjE1O086ODoic3RkQ2xhc3MiOjY6e3M6NToiRmllbGQiO3M6NToidHJhY2siO3M6NDoiVHlwZSI7czoxMDoidGlueWludCgxKSI7czo0OiJOdWxsIjtiOjE7czozOiJLZXkiO3M6MDoiIjtzOjc6IkRlZmF1bHQiO047czo1OiJFeHRyYSI7czowOiIiO31pOjE2O086ODoic3RkQ2xhc3MiOjY6e3M6NToiRmllbGQiO3M6MTI6Im5vdGlmeV9waG9uZSI7czo0OiJUeXBlIjtzOjExOiJ2YXJjaGFyKDIwKSI7czo0OiJOdWxsIjtiOjE7czozOiJLZXkiO3M6MDoiIjtzOjc6IkRlZmF1bHQiO047czo1OiJFeHRyYSI7czowOiIiO31pOjE3O086ODoic3RkQ2xhc3MiOjY6e3M6NToiRmllbGQiO3M6NDoibmFtZSI7czo0OiJUeXBlIjtzOjExOiJ2YXJjaGFyKDUwKSI7czo0OiJOdWxsIjtiOjE7czozOiJLZXkiO3M6MDoiIjtzOjc6IkRlZmF1bHQiO047czo1OiJFeHRyYSI7czowOiIiO31pOjE4O086ODoic3RkQ2xhc3MiOjY6e3M6NToiRmllbGQiO3M6NzoiY29udGFjdCI7czo0OiJUeXBlIjtzOjQ6InRleHQiO3M6NDoiTnVsbCI7YjoxO3M6MzoiS2V5IjtzOjA6IiI7czo3OiJEZWZhdWx0IjtOO3M6NToiRXh0cmEiO3M6MDoiIjt9aToxOTtPOjg6InN0ZENsYXNzIjo2OntzOjU6IkZpZWxkIjtzOjQ6Im5vdGUiO3M6NDoiVHlwZSI7czo0OiJ0ZXh0IjtzOjQ6Ik51bGwiO2I6MTtzOjM6IktleSI7czowOiIiO3M6NzoiRGVmYXVsdCI7TjtzOjU6IkV4dHJhIjtzOjA6IiI7fX19czoyNToiAENhbmFfRGJfTXlTUUxfRGIAX3ByZWZpeCI7Tjt9czoyMToiAENhbmFfTW9kZWwAX2V4dGVuZGVkIjthOjA6e319czoyMzoiAENhbmFfVGFibGUAX3Byb3BlcnRpZXMiO2E6MjE6e3M6MjoiaWQiO3M6MzoiNDE2IjtzOjg6ImlkX3Byb21vIjtzOjM6IjQxNiI7czo3OiJpZF91c2VyIjtzOjQ6IjM1MDgiO3M6NToidmFsdWUiO3M6MToiNSI7czoxMzoiaWRfcmVzdGF1cmFudCI7czozOiIxMDciO3M6NDoiY29kZSI7czo2OiIwUlhOQU8iO3M6NDoiZGF0ZSI7czoxOToiMjAxMy0wNS0yNyAxMjo0ODo1NyI7czo0OiJ0eXBlIjtzOjk6ImdpZnRfY2FyZCI7czo1OiJwaG9uZSI7czoxMDoiMjAzNzcyODE2NyI7czoxMzoiZW1haWxfc3ViamVjdCI7TjtzOjEzOiJlbWFpbF9jb250ZW50IjtOO3M6NToiZW1haWwiO047czoxODoiaWRfb3JkZXJfcmVmZXJlbmNlIjtzOjQ6IjY5MTQiO3M6MjE6ImlkX3Jlc3RhdXJhbnRfcGFpZF9ieSI7TjtzOjc6InBhaWRfYnkiO3M6MTI6IkNSVU5DSEJVVFRPTiI7czoxMDoiY3JlYXRlZF9ieSI7czo2OiJkYW5pZWwiO3M6NToidHJhY2siO3M6MToiMCI7czoxMjoibm90aWZ5X3Bob25lIjtOO3M6NDoibmFtZSI7TjtzOjc6ImNvbnRhY3QiO047czo0OiJub3RlIjtzOjU6IkVtcHR5Ijt9czoyNDoiAENhbmFfVGFibGUAX2pzb25QYXJzaW5nIjtiOjA7czoyMToiAENhbmFfTW9kZWwAX2V4dGVuZGVkIjthOjA6e319fX0=';
		$test = escapeshellarg($test);
		$c = unserialize(base64_decode($test));
var_dump($c->__invoke());exit;
		exit;

		switch ( $this->method() ) {
			
			case 'post':
				
				if ($_SESSION['admin']) {
					switch ( c::getPagePiece( 2 ) ) {
						case 'generate':
							$ids_restaurant = $this->request()['id_restaurant'];
							$value = $this->request()['value'];
							$total = $this->request()['total'];
							$note = $this->request()['note'];
							$id_order_reference = $this->request()['id_order_reference'];
							$paid_by = $this->request()['paid_by'];
							$id_restaurant_paid_by = $this->request()['id_restaurant_paid_by'];
							$id_user = $this->request()['id_user'];
							$created_by = $this->request()['created_by'];
							$track = $this->request()['track'];
							$notify_phone = $this->request()['notify_phone'];
							$name = $this->request()['name'];
							$how_delivery = $this->request()['how_delivery'];
							$contact = $this->request()['contact'];
							$add_as_credit = $this->request()['add_as_credit'];
							$notify_by_email = $this->request()['notify_by_email'];
							$notify_by_sms = $this->request()['notify_by_sms'];

							// Store the ids
							$ids = [];

							foreach( $ids_restaurant as $id_restaurant ){
								if( trim( $id_restaurant ) != '' ){
									for( $i = 1; $i<= $total; $i++) {
										$giftcard = new Crunchbutton_Promo;
										// id_restaurant == * means any restaurant
										if( $id_restaurant == '*' ){
											$giftcard->note = 'This gift is valid to any restaurant!' . "\n" . $note;
										} else {
											$giftcard->id_restaurant = $id_restaurant;
											$giftcard->note = $note;
										}
										$giftcard->code = $giftcard->promoCodeGenerator();
										$giftcard->value = $value;
										if( $id_user ){
											$giftcard->id_user = $id_user;
											$user = Crunchbutton_User::o( $id_user );
											$giftcard->phone =  $user->phone;
											if( $notify_by_email > 0 ){
												$giftcard->email = $user->email; 
												$giftcard->email_subject = 'Congrats, you got a gift card'; 
												$giftcard->email_content = 'Congrats, you got a gift card to ' . Crunchbutton_Promo::TAG_RESTAURANT_NAME . '! To receive it, enter code: ' . Crunchbutton_Promo::TAG_GIFT_CODE . ' in your order notes or click here: ' . Crunchbutton_Promo::TAG_GIFT_URL . '.'; 
											}
										}
										$giftcard->type = Crunchbutton_Promo::TYPE_GIFTCARD;
										$giftcard->note = $note;

										$giftcard->track = $track;
										$giftcard->created_by = $created_by;
										if( $track > 0 ){
											$giftcard->notify_phone = $notify_phone;
											$giftcard->name = $name;
											$giftcard->how_delivery = $how_delivery;
											$giftcard->contact = $contact;
										}
										$giftcard->id_order_reference = $id_order_reference;
										$giftcard->paid_by = $paid_by;
										if( $paid_by == 'other_restaurant' ){
											$giftcard->id_restaurant_paid_by = $id_restaurant_paid_by;
										}
										$giftcard->date = date('Y-m-d H:i:s');
										$giftcard->save();

										$ids[] = $giftcard->id_promo;

										if( $add_as_credit == '1' ){
											if( $id_user ){
												$giftcard->addCredit( $id_user );
											}
										} else {
											if( $id_user ){
												if( $notify_by_email > 0 && $giftcard->email ){
													$giftcard->queNotifyEMAIL();
												}	
												if( $notify_by_sms > 0 && $giftcard->phone ){
													$giftcard->queNotifySMS();
												}	
											} 
										}

										if($id_order_reference) {
											$order = Order::o($id_order_reference);
											if($order->id_order) {
												$support = $order->getSupport();
												if($support->id_support) {
													$support->addNote("Gift card issued #GIFT$giftcard->id_promo.", 'system', 'internal');
												}
											}
										}
									}
								}
							}
							echo json_encode(['success' => join( ',', $ids ) ]);
							break;
					case 'bunchsms':
							$id_restaurant = $this->request()['id_restaurant'];
							$value = $this->request()['value'];
							$phones = $this->request()['phones'];
							$note = $this->request()['note'];
							$id_order_reference = $this->request()['id_order_reference'];
							$paid_by = $this->request()['paid_by'];
							$id_restaurant_paid_by = $this->request()['id_restaurant_paid_by'];
							
							$created_by = $this->request()['created_by'];
							$track = $this->request()['track'];
							$notify_phone = $this->request()['notify_phone'];
							$name = $this->request()['name'];
							$how_delivery = $this->request()['how_delivery'];
							$contact = $this->request()['contact'];

							$phones = explode("\n", $phones);
							foreach ( $phones as $phone ) {
								if( trim( $phone ) != '' ){
									$giftcard = new Crunchbutton_Promo;
									// id_restaurant == * means any restaurant
									if( $id_restaurant == '*' ){
										$giftcard->note = 'This gift is valid to any restaurant!' . "\n" . $note;
									} else {
										$giftcard->id_restaurant = $id_restaurant;
										$giftcard->note = $note;
									}
									$giftcard->code = $giftcard->promoCodeGenerator();
									$giftcard->value = $value;
									$giftcard->phone = $phone;
									$giftcard->type = Crunchbutton_Promo::TYPE_GIFTCARD;
									$giftcard->date = date('Y-m-d H:i:s');
									$giftcard->note = $note;
									$giftcard->track = $track;
									$giftcard->created_by = $created_by;
									if( $track > 0 ){
										$giftcard->notify_phone = $notify_phone;
										$giftcard->name = $name;
										$giftcard->how_delivery = $how_delivery;
										$giftcard->contact = $contact;
									}
									$giftcard->id_order_reference = $id_order_reference;
									$giftcard->paid_by = $paid_by;
									if( $paid_by == 'other_restaurant' ){
										$giftcard->id_restaurant_paid_by = $id_restaurant_paid_by;
									}
									$giftcard->save();
									$giftcard->queNotifySMS();
								}
							}
							echo json_encode(['success' => 'success']);
						break;
					case 'bunchemail':
							$id_restaurant = $this->request()['id_restaurant'];
							$value = $this->request()['value'];
							$emails = $this->request()['emails'];
							$subject = $this->request()['subject'];
							$content = $this->request()['content'];
							$note = $this->request()['note'];
							$id_order_reference = $this->request()['id_order_reference'];
							$paid_by = $this->request()['paid_by'];
							$id_restaurant_paid_by = $this->request()['id_restaurant_paid_by'];
							
							$created_by = $this->request()['created_by'];
							$track = $this->request()['track'];
							$notify_phone = $this->request()['notify_phone'];
							$name = $this->request()['name'];
							$how_delivery = $this->request()['how_delivery'];
							$contact = $this->request()['contact'];
							
							$emails = explode("\n", $emails);
							foreach ( $emails as $email ) {
								if( trim( $email ) != '' ){
									$giftcard = new Crunchbutton_Promo;
									// id_restaurant == * means any restaurant
									if( $id_restaurant == '*' ){
										$giftcard->note = 'This gift is valid to any restaurant!' . "\n" . $note;
									} else {
										$giftcard->id_restaurant = $id_restaurant;
										$giftcard->note = $note;
									}
									$giftcard->code = $giftcard->promoCodeGenerator();
									$giftcard->value = $value;
									$giftcard->email = $email;
									$giftcard->email_subject = $subject;
									$giftcard->email_content = $content;
									$giftcard->type = Crunchbutton_Promo::TYPE_GIFTCARD;
									$giftcard->date = date('Y-m-d H:i:s');
									$giftcard->note = $note;
									$giftcard->track = $track;
									$giftcard->created_by = $created_by;
									if( $track > 0 ){
										$giftcard->notify_phone = $notify_phone;
										$giftcard->name = $name;
										$giftcard->how_delivery = $how_delivery;
										$giftcard->contact = $contact;
									}
									$giftcard->id_order_reference = $id_order_reference;
									$giftcard->paid_by = $paid_by;
									if( $paid_by == 'other_restaurant' ){
										$giftcard->id_restaurant_paid_by = $id_restaurant_paid_by;
									}
									$giftcard->save();
									$giftcard->queNotifyEMAIL();
								}
							}
							echo json_encode(['success' => 'success']);
						break;
					case 'relateuser':
							$giftcard = Crunchbutton_Promo::o( $this->request()['id_promo'] );
							if( $giftcard->id_promo ){
								$giftcard->id_user =  $this->request()['id_user'];
								$giftcard->save();
								$giftcard->phone =  $giftcard->user()->phone;
								$giftcard->save();
								echo $giftcard->json();
							} else {
								echo json_encode(['error' => 'error']);
							}
							break;
					case 'email':
							$giftcard = Crunchbutton_Promo::o( $this->request()['id_promo'] );
							if( $giftcard->id_promo ){
								$giftcard->queNotifyEMAIL();
								echo $giftcard->json();
							} else {
								echo json_encode(['error' => 'error']);
							}
							break;
					case 'sms':
							$giftcard = Crunchbutton_Promo::o( $this->request()['id_promo'] );
							if( $giftcard->id_promo ){
								$giftcard->queNotifySMS();
								echo $giftcard->json();
							} else {
								echo json_encode(['error' => 'error']);
							}
							break;
					}
				}

				if ( c::getPagePiece(2) == 'code' ) {
					// Get the giftcard (promo) by code
					$giftcard = Crunchbutton_Promo::byCode( $this->request()['code'] );
					// Check if the giftcard is valid
					if( $giftcard->id_promo ){
						// Check if the giftcard was already used
						if( Crunchbutton_Promo::giftWasAlreadyUsed( $giftcard->id_promo ) ){
							echo json_encode(['error' => 'gift card already used']);
						} else {
							// It the gift has a user_id just this user will be able to use it
							if( $giftcard->id_user && $giftcard->id_user != c::user()->id_user ){
								echo json_encode(['error' => 'invalid gift card']);
								exit;		
							}
							// Add credit to user
							$credit = $giftcard->addCredit( c::user()->id_user );
							if( $credit->id_credit ){
								if( $credit->id_restaurant ){
									echo json_encode( [ 'success' => [ 'value' => $credit->value, 'restaurant' => $credit->restaurant()->name, 'id_restaurant' => $credit->restaurant()->id_restaurant ] ] );	
								} else {
									echo json_encode( [ 'success' => [ 'value' => $credit->value ] ] );
								}
							} else {
								echo json_encode(['error' => 'gift card not added']);
							}
						}
					} else {
						echo json_encode(['error' => 'invalid gift card']);
					}
				}
 				
				if ( c::getPagePiece(2) == 'validate' ) {

					$code = $this->request()['code'];
					// Get the giftcard (promo) by code
					$giftcard = Crunchbutton_Promo::byCode( $code);
					// Check if the giftcard is valid
					if( $giftcard->id_promo ){
						// Check if the giftcard was already used
						if( Crunchbutton_Promo::giftWasAlreadyUsed( $giftcard->id_promo ) ){
							echo json_encode(['error' => 'gift card already used', 'giftcard' => $code ]);
						} else {
							// It the gift has a user_id just this user will be able to use it
							if( $giftcard->id_user && $giftcard->id_user != c::user()->id_user ){
								echo json_encode(['error' => 'invalid gift card', 'giftcard' => $code ]);
								exit;		
							}
							echo json_encode( [ 'success' => [ 'value' => $giftcard->value, 'id_restaurant' => $giftcard->id_restaurant, 'giftcard' => $code ] ] );
						}
					} else {
						echo json_encode(['error' => 'invalid gift card', 'giftcard' => $code ] );
					}
				}

			break;
			default:
				echo json_encode(['error' => 'invalid object']);
			break;
		}
	}
}