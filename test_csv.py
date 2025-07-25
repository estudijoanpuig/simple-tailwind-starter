import csv
with open('compartit 1tr-2025 - VENDA.csv', 'r', encoding='utf-8') as file:
    reader = csv.reader(file)
    for row in reader:
        print(row)