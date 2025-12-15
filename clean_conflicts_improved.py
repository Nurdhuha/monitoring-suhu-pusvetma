import os
import re

def clean_merge_conflicts_improved(file_path):
    """Remove Git merge conflict markers from a file - improved version"""
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            lines = f.readlines()
        
        cleaned_lines = []
        in_conflict = False
        in_head_section = False
        skip_until_end = False
        
        for line in lines:
            if line.startswith('<<<<<<< HEAD'):
                in_conflict = True
                in_head_section = True
                continue
            elif line.startswith('=======') and in_conflict:
                in_head_section = False
                skip_until_end = True
                continue
            elif line.startswith('>>>>>>> master') and in_conflict:
                in_conflict = False
                skip_until_end = False
                continue
            
            # Only keep lines from HEAD section, skip master section
            if in_conflict:
                if in_head_section:
                    cleaned_lines.append(line)
            else:
                cleaned_lines.append(line)
        
        with open(file_path, 'w', encoding='utf-8') as f:
            f.writelines(cleaned_lines)
        
        return True
    except Exception as e:
        print(f"Error: {file_path}: {str(e)}")
        return False

# List of known files with conflicts
files_to_clean = [
    r'd:\Coding\monitoring-suhu-pusvetma-main\monitoring-suhu-pusvetma-main\resources\views\admin\data-suhu\index.blade.php',
    r'd:\Coding\monitoring-suhu-pusvetma-main\monitoring-suhu-pusvetma-main\resources\views\superadmin\data-suhu\index.blade.php',
]

files_cleaned = 0
for file_path in files_to_clean:
    if os.path.exists(file_path):
        if clean_merge_conflicts_improved(file_path):
            files_cleaned += 1
            print(f"Cleaned: {file_path}")
    else:
        print(f"File not found: {file_path}")

print(f"\nTotal files cleaned: {files_cleaned}")
